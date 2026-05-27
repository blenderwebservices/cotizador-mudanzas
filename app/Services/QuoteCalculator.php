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

        // 6. Travel, loading & packing times
        $tiempoEmpaqueHoras = $tiempoEmpaqueTotalMin / 60;
        $tiempoCargaHoras = $volumenTotalM3 * (float) config('mudanzas.factor_tiempo_carga_por_m3');
        $tiempoTrabajoTotalHoras = $tiempoEmpaqueHoras + $tiempoCargaHoras + $tiempoTrasladoHoras;

        // 7. Costs breakdown
        $comidaTrabajadoresCosto = $personasSugeridas * (float) config('mudanzas.precio_comida_por_persona');
        $salariosCosto = $personasSugeridas * $tiempoTrabajoTotalHoras * (float) config('mudanzas.salario_por_hora_por_persona');
        $materialEmpaqueCosto = $costoEmpaqueTotal;

        // 8. Financial totals
        $gastosTotales = $costoCombustible + $materialEmpaqueCosto + $comidaTrabajadoresCosto + $salariosCosto;
        
        // Suggested Price based on 50% expenses / 50% profit margin
        $precioSugerido = $gastosTotales * 2;
        
        // Apply minimum tariff
        $tarifaMinima = (float) config('mudanzas.tarifa_minima');
        if ($precioSugerido < $tarifaMinima) {
            $precioSugerido = $tarifaMinima;
        }

        $gananciaEstimada = $precioSugerido - $gastosTotales;

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
            'tiempo_empaque_total_min' => $tiempoEmpaqueTotalMin,
            'tiempo_carga_horas' => $tiempoCargaHoras,
            'material_empaque_costo' => $materialEmpaqueCosto,
            'comida_trabajadores_costo' => $comidaTrabajadoresCosto,
            'salarios_costo' => $salariosCosto,
            'gastos_totales' => $gastosTotales,
            'ganancia_estimada' => $gananciaEstimada,
            'precio_sugerido' => $precioSugerido,
            'items_breakdown' => $itemsBreakdown,
        ];
    }

    /**
     * Ask Gemini API to estimate the driving distance and travel time.
     */
    private function estimateDistanceAndTime(string $origin, string $destination): array
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
