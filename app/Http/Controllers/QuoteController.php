<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Agent;
use App\Services\QuoteCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class QuoteController extends Controller
{
    protected QuoteCalculator $calculator;

    public function __construct(QuoteCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Get active items list for the frontend selector.
     */
    public function getItems()
    {
        $items = Item::where('activo', true)->orderBy('orden')->get();
        return response()->json($items);
    }

    /**
     * Store a new quote request, perform calculations, and return results.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'origin' => 'required|string',
            'destination' => 'required|string',
            'elevatorStart' => 'nullable|string',
            
            // Drivers logísticos del costeo ABC
            'pisos_origen' => 'nullable|integer|min:1',
            'distancia_caminata_origen_m' => 'nullable|integer|min:0',
            'pisos_destino' => 'nullable|integer|min:1',
            'ascensor_destino' => 'nullable|string|in:yes,no',
            'distancia_caminata_destino_m' => 'nullable|integer|min:0',

            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.count' => 'required|integer|min:0',
            'items.*.volumen_m3' => 'nullable|numeric|min:0',
            'items.*.peso_kg' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Run moving calculation service
            $calcResults = $this->calculator->calculate(
                [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'origin' => $validated['origin'],
                    'destination' => $validated['destination'],
                    'elevatorStart' => $validated['elevatorStart'] ?? null,
                    
                    // Drivers ABC
                    'pisos_origen' => $validated['pisos_origen'] ?? 1,
                    'distancia_caminata_origen_m' => $validated['distancia_caminata_origen_m'] ?? 10,
                    'pisos_destino' => $validated['pisos_destino'] ?? 1,
                    'ascensor_destino' => $validated['ascensor_destino'] ?? true,
                    'distancia_caminata_destino_m' => $validated['distancia_caminata_destino_m'] ?? 10,
                ],
                $validated['items']
            );

            // Assign a random active agent if available
            $randomAgent = Agent::where('status', 'active')->inRandomOrder()->first();

            // Create Quote record
            $quote = Quote::create([
                'nombre_cliente' => $validated['name'],
                'email_cliente' => $validated['email'],
                'telefono_cliente' => $validated['phone'] ?? null,
                'origen' => $validated['origin'],
                'destino' => $validated['destination'],
                
                // Drivers logísticos ABC guardados en BD
                'pisos_origen' => $calcResults['pisos_origen'],
                'distancia_caminata_origen_m' => $calcResults['distancia_caminata_origen_m'],
                'pisos_destino' => $calcResults['pisos_destino'],
                'ascensor_destino' => $calcResults['ascensor_destino'],
                'distancia_caminata_destino_m' => $calcResults['distancia_caminata_destino_m'],
                
                // Desglose de Actividades ABC
                'costo_actividad_comercial' => $calcResults['costo_actividad_comercial'],
                'costo_actividad_embalaje' => $calcResults['costo_actividad_embalaje'],
                'costo_actividad_carga' => $calcResults['costo_actividad_carga'],
                'costo_actividad_transporte' => $calcResults['costo_actividad_transporte'],
                'costo_actividad_descarga' => $calcResults['costo_actividad_descarga'],

                'distancia_km' => $calcResults['distancia_km'],
                'tiempo_traslado_horas' => $calcResults['tiempo_traslado_horas'],
                'volumen_total_m3' => $calcResults['volumen_total_m3'],
                'costo_empaque_total' => $calcResults['costo_empaque_total'],
                
                'vehiculo_sugerido_id' => $calcResults['vehiculo_sugerido_id'],
                'personas_sugeridas' => $calcResults['personas_sugeridas'],
                
                'combustible_l' => $calcResults['combustible_l'],
                'costo_combustible' => $calcResults['costo_combustible'],
                'tiempo_empaque_total_min' => $calcResults['tiempo_empaque_total_min'],
                'tiempo_carga_horas' => $calcResults['tiempo_carga_horas'],
                'material_empaque_costo' => $calcResults['material_empaque_costo'],
                'comida_trabajadores_costo' => $calcResults['comida_trabajadores_costo'],
                'salarios_costo' => $calcResults['salarios_costo'],
                
                'gastos_totales' => $calcResults['gastos_totales'],
                'ganancia_estimada' => $calcResults['ganancia_estimada'],
                'precio_sugerido' => $calcResults['precio_sugerido'],
                
                'agent_id' => $randomAgent ? $randomAgent->id : null,
                'detalles_json' => [
                    'elevatorStart' => $validated['elevatorStart'] ?? null,
                    'factor_tiempo_carga' => config('mudanzas.factor_tiempo_carga_por_m3'),
                    'viatico_comida_unitario' => config('mudanzas.precio_comida_por_persona'),
                    'salario_hora_unitario' => config('mudanzas.salario_por_hora_por_persona'),
                ]
            ]);

            // Save Quote items
            foreach ($validated['items'] as $itemInput) {
                if ($itemInput['count'] > 0) {
                    QuoteItem::create([
                        'quote_id' => $quote->id,
                        'item_id' => $itemInput['item_id'],
                        'cantidad' => $itemInput['count'],
                        'volumen_m3' => $itemInput['volumen_m3'] ?? null,
                        'peso_kg' => $itemInput['peso_kg'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'quote_id' => $quote->id,
                'transaction_id' => 'MDG-' . str_pad($quote->id, 5, '0', STR_PAD_LEFT) . '-FT',
                'results' => [
                    'precio_sugerido' => $calcResults['precio_sugerido'],
                    'volumen_total_m3' => $calcResults['volumen_total_m3'],
                    'vehiculo_sugerido' => $calcResults['vehiculo_sugerido_nombre'],
                    'personas_sugeridas' => $calcResults['personas_sugeridas'],
                    'distancia_km' => $calcResults['distancia_km'],
                    'tiempo_traslado_horas' => $calcResults['tiempo_traslado_horas'],
                    'tiempo_empaque_total_min' => $calcResults['tiempo_empaque_total_min'],
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save quote request: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar la cotización. Por favor intente más tarde.'
            ], 500);
        }
    }

    /**
     * Generate PDF for client view (hide internal cost details)
     */
    public function clientPdf($quoteId)
    {
        $quote = Quote::with('items.item')->findOrFail($quoteId);
        $data = [
            'quote' => $quote,
            'items' => $quote->items,
        ];
        $pdf = Pdf::loadView('pdf.client', $data);
        return $pdf->stream('cotizacion_cliente_' . $quote->id . '.pdf');
    }

    /**
     * Generate PDF for admin view (include detailed costs and profit suggestion)
     */
    public function adminPdf($quoteId)
    {
        $quote = Quote::with('items.item')->findOrFail($quoteId);
        $data = [
            'quote' => $quote,
            'items' => $quote->items,
        ];
        $pdf = Pdf::loadView('pdf.admin', $data);
        return $pdf->stream('cotizacion_admin_' . $quote->id . '.pdf');
    }
}

