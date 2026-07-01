<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\Item;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QuoteCalculator
{
    /**
     * Calculate all metrics for a quote request.
     *
     * @param array $clientData  ['name', 'email', 'phone', 'origin', 'destination', 'elevatorStart']
     * @param array $selectedItems  Array of ['item_id' => X, 'count' => Y, 'volumen_m3' => Z, 'peso_kg' => W]
     * @return array
     */
    public function calculate(array $clientData, array $selectedItems): array
    {
        // 1. Estimate Distance and Travel Time via Gemini
        $travelEstimation = $this->estimateDistanceAndTime($clientData['origin'], $clientData['destination']);
        $distanciaKm = $travelEstimation['distancia_km'];
        $tiempoTrasladoHoras = $travelEstimation['tiempo_traslado_horas'];

        // 2. Sum Volumes & Packing Costs of Items
        $volumenTotalM3 = 0;
        $costoEmpaqueTotal = 0;
        $tiempoEmpaqueTotalMin = 0;
        $itemsBreakdown = [];

        foreach ($selectedItems as $selection) {
            $itemId = $selection['item_id'];
            $count = $selection['count'];
            
            if ($count <= 0) {
                continue;
            }

            $item = Item::find($itemId);
            if (!$item) {
                continue;
            }

            // Determine volume (use custom client volume if available and this item allows optional details)
            $itemVolume = ($item->permite_detalles_opcionales && isset($selection['volumen_m3']) && $selection['volumen_m3'] > 0)
                ? (float) $selection['volumen_m3']
                : (float) $item->tamano_volumetrico;

            $itemWeight = ($item->permite_detalles_opcionales && isset($selection['peso_kg']) && $selection['peso_kg'] > 0)
                ? (float) $selection['peso_kg']
                : null;

            // Packing cost per item * count
            $packingCost = (float) $item->costo_empaque * $count;
            // Packing time in minutes
            $packingTime = (int) $item->tiempo_empaque * $count;
            // Total volume for this item type
            $totalVolume = $itemVolume * $count;

            $volumenTotalM3 += $totalVolume;
            $costoEmpaqueTotal += $packingCost;
            $tiempoEmpaqueTotalMin += $packingTime;

            $itemsBreakdown[] = [
                'item_id' => $item->id,
                'nombre' => $item->nombre,
                'cantidad' => $count,
                'volumen_unitario_m3' => $itemVolume,
                'peso_unitario_kg' => $itemWeight,
                'volumen_total_m3' => $totalVolume,
                'costo_empaque_total' => $packingCost,
                'tiempo_empaque_total_min' => $packingTime,
                'requiere_desarmarse' => $item->requiere_desarmarse,
            ];
        }

        // 3. Suggest Vehicle
        $vehicles = Vehicle::where('activo', true)
            ->orderBy('capacidad_m3', 'asc')
            ->get();
        
        $suggestedVehicle = null;
        foreach ($vehicles as $vehicle) {
            if ($vehicle->capacidad_m3 >= $volumenTotalM3) {
                $suggestedVehicle = $vehicle;
                break;
            }
        }
        // Fallback to the largest vehicle if volume exceeds all trucks, or if no vehicle in DB
        if (!$suggestedVehicle && $vehicles->isNotEmpty()) {
            $suggestedVehicle = $vehicles->last();
        }

        // 4. Suggest Workers
        $personasSugeridas = $this->determineWorkersCount($volumenTotalM3);

        // 5. Fuel calculation
        $fuelNeededL = 0;
        $costoCombustible = 0;
        if ($suggestedVehicle) {
            $fuelNeededL = $distanciaKm / (float) $suggestedVehicle->consumo_kml;
            $costoCombustible = $fuelNeededL * (float) config('mudanzas.precio_combustible_por_litro');
        }

        // 6. Extract ABC logisitical drivers
        $pisosOrigen = (int) ($clientData['pisos_origen'] ?? 1);
        $caminataOrigen = (int) ($clientData['distancia_caminata_origen_m'] ?? 10);
        $ascensorOrigen = ($clientData['elevatorStart'] ?? 'no') === 'yes';

        $pisosDestino = (int) ($clientData['pisos_destino'] ?? 1);
        $ascensorDestino = ($clientData['ascensor_destino'] ?? 'yes') === 'yes';
        $caminataDestino = (int) ($clientData['distancia_caminata_destino_m'] ?? 10);

        // 7. Calculate the 5 ABC Activities

        // Actividad A: Comercial y Planificación (Fija)
        $costoComercial = (float) config('mudanzas.abc.tarifa_comercial_fija', 150.00);

        // Actividad B: Embalaje y Preparación
        // Costo de materiales de empaque
        $materialEmpaqueCosto = $costoEmpaqueTotal;
        // Tiempo de desarme por muebles marcados
        $itemsRequierenDesarme = 0;
        foreach ($itemsBreakdown as $ib) {
            if ($ib['requiere_desarmarse']) {
                $itemsRequierenDesarme += $ib['cantidad'];
            }
        }
        $tiempoDesarmeMin = $itemsRequierenDesarme * (int) config('mudanzas.abc.desarme_tiempo_minutos', 15);
        $tiempoEmpaqueTotalMinConDesarme = $tiempoEmpaqueTotalMin + $tiempoDesarmeMin;
        $tiempoEmpaqueHoras = $tiempoEmpaqueTotalMinConDesarme / 60;
        // Mano de obra de embalaje/desarme
        $manoObraEmpaque = $personasSugeridas * $tiempoEmpaqueHoras * (float) config('mudanzas.salario_por_hora_por_persona', 150.00);
        $costoEmbalaje = $materialEmpaqueCosto + $manoObraEmpaque;

        // Actividad C: Carga y Estiba (con recargos por pisos y caminata)
        $costoCargaBase = $volumenTotalM3 * (float) config('mudanzas.abc.costo_carga_base_m3', 60.00);
        $recargoEscalerasCarga = 0.0;
        if (!$ascensorOrigen && $pisosOrigen > 1) {
            $recargoEscalerasCarga = $volumenTotalM3 * ($pisosOrigen - 1) * (float) config('mudanzas.abc.tarifa_escalera_m3_piso', 25.00);
        }
        $recargoCaminataCarga = 0.0;
        if ($caminataOrigen > 10) {
            $recargoCaminataCarga = $volumenTotalM3 * ($caminataOrigen - 10) * (float) config('mudanzas.abc.tarifa_caminata_m3_metro', 1.50);
        }
        $costoCarga = $costoCargaBase + $recargoEscalerasCarga + $recargoCaminataCarga;

        // Actividad D: Transporte (Conducción)
        $vehiculoId = $suggestedVehicle ? $suggestedVehicle->id : 1;
        $costoKmFactor = (float) (config("mudanzas.abc.costo_por_km_vehiculo.{$vehiculoId}") ?? 6.00);
        $costoDepreciacion = $distanciaKm * $costoKmFactor;
        // Mano de obra de traslado
        $tiempoViajeManoObra = $personasSugeridas * $tiempoTrasladoHoras * (float) config('mudanzas.salario_por_hora_por_persona', 150.00);
        $costoTransporte = $costoCombustible + $costoDepreciacion + $tiempoViajeManoObra;

        // Actividad E: Descarga y Desembalaje (con recargos por pisos y caminata en destino)
        $costoDescargaBase = $volumenTotalM3 * (float) config('mudanzas.abc.costo_descarga_base_m3', 60.00);
        $recargoEscalerasDescarga = 0.0;
        if (!$ascensorDestino && $pisosDestino > 1) {
            $recargoEscalerasDescarga = $volumenTotalM3 * ($pisosDestino - 1) * (float) config('mudanzas.abc.tarifa_escalera_m3_piso', 25.00);
        }
        $recargoCaminataDescarga = 0.0;
        if ($caminataDestino > 10) {
            $recargoCaminataDescarga = $volumenTotalM3 * ($caminataDestino - 10) * (float) config('mudanzas.abc.tarifa_caminata_m3_metro', 1.50);
        }
        $costoDescarga = $costoDescargaBase + $recargoEscalerasDescarga + $recargoCaminataDescarga;

        // 8. Financial totals
        $gastosTotales = $costoComercial + $costoEmbalaje + $costoCarga + $costoTransporte + $costoDescarga;
        
        // Suggested Price based on configurable profit margin
        $precioSugerido = $gastosTotales * (1.0 + (float) config('mudanzas.ganancia_porcentaje', 0.50));
        
        // Apply minimum tariff
        $tarifaMinima = (float) config('mudanzas.tarifa_minima');
        if ($precioSugerido < $tarifaMinima) {
            $precioSugerido = $tarifaMinima;
        }

        $gananciaEstimada = $precioSugerido - $gastosTotales;

        // Tiempos logísticos para desglose tradicional (compatibilidad)
        $tiempoCargaHoras = $volumenTotalM3 * (float) config('mudanzas.factor_tiempo_carga_por_m3');
        $comidaTrabajadoresCosto = $personasSugeridas * (float) config('mudanzas.precio_comida_por_persona');
        $salariosCosto = $manoObraEmpaque + $tiempoViajeManoObra; // salarios directos de embalaje y traslado

        return [
            'distancia_km' => $distanciaKm,
            'tiempo_traslado_horas' => $tiempoTrasladoHoras,
            'volumen_total_m3' => $volumenTotalM3,
            'costo_empaque_total' => $costoEmpaqueTotal,
            'vehiculo_sugerido_id' => $suggestedVehicle ? $suggestedVehicle->id : null,
            'vehiculo_sugerido_nombre' => $suggestedVehicle ? $suggestedVehicle->nombre : 'N/A',
            'personas_sugeridas' => $personasSugeridas,
            'combustible_l' => $fuelNeededL,
            'costo_combustible' => $costoCombustible,
            'tiempo_empaque_total_min' => $tiempoEmpaqueTotalMinConDesarme,
            'tiempo_carga_horas' => $tiempoCargaHoras,
            'material_empaque_costo' => $materialEmpaqueCosto,
            'comida_trabajadores_costo' => $comidaTrabajadoresCosto,
            'salarios_costo' => $salariosCosto,
            'gastos_totales' => $gastosTotales,
            'ganancia_estimada' => $gananciaEstimada,
            'precio_sugerido' => $precioSugerido,
            'items_breakdown' => $itemsBreakdown,

            // Atributos de costeo ABC
            'pisos_origen' => $pisosOrigen,
            'distancia_caminata_origen_m' => $caminataOrigen,
            'pisos_destino' => $pisosDestino,
            'ascensor_destino' => $ascensorDestino,
            'distancia_caminata_destino_m' => $caminataDestino,
            'costo_actividad_comercial' => $costoComercial,
            'costo_actividad_embalaje' => $costoEmbalaje,
            'costo_actividad_carga' => $costoCarga,
            'costo_actividad_transporte' => $costoTransporte,
            'costo_actividad_descarga' => $costoDescarga,
        ];
    }

    /**
     * Ask Google Maps Distance Matrix API or fallback to Gemini API to estimate the driving distance and travel time.
     */
    private function estimateDistanceAndTime(string $origin, string $destination): array
    {
        $googleKey = config('services.google.maps_api_key');
        if (!empty($googleKey)) {
            try {
                $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                    'origins' => $origin,
                    'destinations' => $destination,
                    'key' => $googleKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'OK' && !empty($data['rows'][0]['elements'][0])) {
                        $element = $data['rows'][0]['elements'][0];
                        if (($element['status'] ?? '') === 'OK') {
                            $distanceMeters = $element['distance']['value'];
                            $durationSeconds = $element['duration']['value'];
                            return [
                                'distancia_km' => max(1.0, round($distanceMeters / 1000, 2)),
                                'tiempo_traslado_horas' => max(0.2, round($durationSeconds / 3600, 2)),
                            ];
                        } else {
                            Log::warning('Google Maps Distance Matrix element status is not OK: ' . ($element['status'] ?? 'unknown'));
                        }
                    } else {
                        Log::error('Google Maps Distance Matrix API returned error status: ' . ($data['status'] ?? 'unknown'));
                    }
                } else {
                    Log::error('Google Maps Distance Matrix HTTP request failed.', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Exception caught during Google Maps distance calculation: ' . $e->getMessage());
            }
        } else {
            Log::info('Google Maps API key is not configured. Falling back to Gemini API.');
        }

        // Fallback to Gemini
        return $this->estimateDistanceAndTimeViaGemini($origin, $destination);
    }

    /**
     * Ask Gemini API to estimate the driving distance and travel time.
     */
    private function estimateDistanceAndTimeViaGemini(string $origin, string $destination): array
    {
        $apiKey = config('gemini.api_key');
        $model = config('gemini.model', 'gemini-3.5-flash-lite-preview');
        
        if (empty($apiKey)) {
            Log::warning('Gemini API key is not configured. Falling back to default distance estimation.');
            return $this->getDefaultDistanceEstimation();
        }

        $prompt = "Actúa como una base de datos geográfica de precisión. Estima la distancia real por carretera (en kilómetros) y el tiempo de traslado típico (en horas) para un camión de mudanzas que circula de la dirección de origen a la dirección de destino.
        Origen: \"{$origin}\"
        Destino: \"{$destination}\"
        Debes retornar estrictamente un objeto JSON que contenga los campos 'distancia_km' (número) y 'tiempo_traslado_horas' (número).
        No incluyas explicaciones, no incluyas marcas de markdown de bloque de código, solo el objeto JSON crudo.";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        try {
            $response = Http::timeout(10)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $textResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                // Clean any accidental markdown code fence that may wrap the JSON
                $cleanJson = trim($textResponse);
                if (str_starts_with($cleanJson, '```')) {
                    $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
                    $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);
                }
                
                $parsed = json_decode($cleanJson, true);
                if (isset($parsed['distancia_km']) && isset($parsed['tiempo_traslado_horas'])) {
                    return [
                        'distancia_km' => max(1.0, (float) $parsed['distancia_km']),
                        'tiempo_traslado_horas' => max(0.2, (float) $parsed['tiempo_traslado_horas']),
                    ];
                }
            }
            
            Log::error('Gemini API returned an invalid response format or failed.', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('Exception caught during Gemini distance estimation: ' . $e->getMessage());
        }

        return $this->getDefaultDistanceEstimation();
    }

    /**
     * Default fallback values if Gemini API fails.
     */
    private function getDefaultDistanceEstimation(): array
    {
        return [
            'distancia_km' => 15.00,
            'tiempo_traslado_horas' => 1.00,
        ];
    }

    /**
     * Determine suggested workers count based on volume.
     */
    private function determineWorkersCount(float $volume): int
    {
        if ($volume <= 10.0) {
            return 2;
        } elseif ($volume <= 25.0) {
            return 3;
        } elseif ($volume <= 50.0) {
            return 4;
        } elseif ($volume <= 70.0) {
            return 5;
        } else {
            return 6;
        }
    }
}
