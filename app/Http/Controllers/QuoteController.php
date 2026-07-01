<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Agent;
use App\Services\QuoteCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;


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

    /**
     * Generate Excel for admin view
     */
    public function adminExcel($quoteId)
    {
        $quote = Quote::with(['items.item', 'vehiculoSugerido', 'agent'])->findOrFail($quoteId);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detalle Cotización');
        
        $sheet->setShowGridlines(true);

        $brandRed = 'ED3426';
        $borderGray = 'CBD5E1';

        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // Header Title Block
        $sheet->setCellValue('A1', 'COTIZACIÓN ADMINISTRATIVA - DETALLE');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB($brandRed);
        
        $sheet->setCellValue('A2', 'Mudanzas Hermanos Monroy');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);
        
        $folio = 'MDG-' . str_pad($quote->id, 5, '0', STR_PAD_LEFT);
        $sheet->setCellValue('A3', 'Folio: ' . $folio . ' | Fecha: ' . ($quote->created_at ? $quote->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i')));
        $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('475569');

        // --- SECTION 1: DATOS GENERALES ---
        $sheet->setCellValue('A5', '1. DATOS DEL CLIENTE Y SERVICIO');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB($brandRed);
        
        $sheet->setCellValue('A6', 'Cliente:');
        $sheet->setCellValue('B6', $quote->nombre_cliente);
        $sheet->setCellValue('A7', 'Correo:');
        $sheet->setCellValue('B7', $quote->email_cliente);
        $sheet->setCellValue('A8', 'Teléfono:');
        $sheet->setCellValue('B8', $quote->telefono_cliente ?? 'N/A');
        
        $sheet->setCellValue('A9', 'Origen:');
        $elevatorStartText = ($quote->detalles_json['elevatorStart'] ?? 'no') === 'yes' ? 'Sí' : 'No';
        $sheet->setCellValue('B9', $quote->origen . " (Piso {$quote->pisos_origen}, Ascensor: {$elevatorStartText}, Caminata: {$quote->distancia_caminata_origen_m}m)");
        
        $sheet->setCellValue('A10', 'Destino:');
        $elevatorDestText = $quote->ascensor_destino ? 'Sí' : 'No';
        $sheet->setCellValue('B10', $quote->destino . " (Piso {$quote->pisos_destino}, Ascensor: {$elevatorDestText}, Caminata: {$quote->distancia_caminata_destino_m}m)");

        $sheet->getStyle('A6:A10')->getFont()->setBold(true);

        // --- SECTION 2: DRIVERS LOGÍSTICOS ---
        $sheet->setCellValue('D5', '2. PARÁMETROS LOGÍSTICOS');
        $sheet->getStyle('D5')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB($brandRed);

        $sheet->setCellValue('D6', 'Distancia Estimada:');
        $sheet->setCellValue('E6', $quote->distancia_km . ' km');
        $sheet->setCellValue('D7', 'Tiempo Traslado:');
        $sheet->setCellValue('E7', $quote->tiempo_traslado_horas . ' hrs');
        $sheet->setCellValue('D8', 'Volumen Total:');
        $sheet->setCellValue('E8', $quote->volumen_total_m3 . ' m³');
        $sheet->setCellValue('D9', 'Personal Sugerido:');
        $sheet->setCellValue('E9', $quote->personas_sugeridas . ' cargadores');
        $sheet->setCellValue('D10', 'Vehículo Sugerido:');
        $sheet->setCellValue('E10', $quote->vehiculoSugerido ? $quote->vehiculoSugerido->nombre : 'N/A');
        
        $sheet->getStyle('D6:D10')->getFont()->setBold(true);

        // --- SECTION 3: DESGLOSE DE COSTOS (MODELO ABC) ---
        $sheet->setCellValue('A13', '3. DESGLOSE DE COSTOS (MODELO ABC)');
        $sheet->getStyle('A13')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB($brandRed);

        $costos = [
            ['Actividad A: Comercial y Planificación', $quote->costo_actividad_comercial],
            ['Actividad B: Embalaje y Preparación', $quote->costo_actividad_embalaje],
            ['Actividad C: Carga y Estiba', $quote->costo_actividad_carga],
            ['Actividad D: Transporte (Conducción)', $quote->costo_actividad_transporte],
            ['Actividad E: Descarga y Desembalaje', $quote->costo_actividad_descarga],
            ['Costo Operativo Total (Gastos)', $quote->gastos_totales, true, 'F1F5F9'],
            ['Ganancia Estimada', $quote->ganancia_estimada, true, 'DCFCE7'],
            ['PRECIO SUGERIDO AL CLIENTE', $quote->precio_sugerido, true, 'FEE2E2', $brandRed],
        ];

        $row = 14;
        foreach ($costos as $c) {
            $sheet->setCellValue('A' . $row, $c[0]);
            $sheet->setCellValue('B' . $row, $c[1]);
            
            $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');

            if (isset($c[2]) && $c[2] === true) {
                $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
                if (isset($c[3])) {
                    $sheet->getStyle('A' . $row . ':B' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($c[3]);
                }
                if (isset($c[4])) {
                    $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->getColor()->setRGB($c[4]);
                }
            }
            $row++;
        }

        $costTableRange = 'A14:B' . ($row - 1);
        $sheet->getStyle($costTableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($borderGray);

        // --- SECTION 4: INVENTARIO DETALLADO ---
        $row += 2;
        $sheet->setCellValue('A' . $row, '4. ITEMS SELECCIONADOS (INVENTARIO)');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(11)->getColor()->setRGB($brandRed);
        
        $row++;
        $startInventoryRow = $row;
        
        $headers = ['Artículo / Item', 'Cantidad', 'Volumen Unitario (m³)', 'Peso Unitario (kg)', 'Costo Empaque Unitario'];
        $cols = ['A', 'B', 'C', 'D', 'E'];
        
        foreach ($headers as $index => $header) {
            $col = $cols[$index];
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2E8F0');
        }
        
        $row++;
        
        $items = $quote->items;
        foreach ($items as $qi) {
            $sheet->setCellValue('A' . $row, $qi->item->nombre);
            $sheet->setCellValue('B' . $row, $qi->cantidad);
            $sheet->setCellValue('C' . $row, $qi->volumen_m3 ?? $qi->item->tamano_volumetrico ?? 0);
            $sheet->setCellValue('D' . $row, $qi->peso_kg ?? 0);
            $sheet->setCellValue('E' . $row, $qi->item->costo_empaque ?? 0);

            $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('$#,##0.00');
            
            $row++;
        }
        
        $sheet->setCellValue('A' . $row, 'TOTALES');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        
        $sheet->setCellValue('B' . $row, "=SUM(B" . ($startInventoryRow + 1) . ":B" . ($row - 1) . ")");
        $sheet->setCellValue('C' . $row, "=SUM(C" . ($startInventoryRow + 1) . ":C" . ($row - 1) . ")");
        $sheet->setCellValue('D' . $row, "=SUM(D" . ($startInventoryRow + 1) . ":D" . ($row - 1) . ")");
        
        $sheet->getStyle('B' . $row . ':D' . $row)->getFont()->setBold(true);
        $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        
        $sheet->getStyle('A' . $row . ':E' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F1F5F9');

        $inventoryTableRange = 'A' . $startInventoryRow . ':E' . $row;
        $sheet->getStyle($inventoryTableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($borderGray);

        foreach (range('A', 'E') as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'cotizacion_admin_' . $quote->id . '_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Get address suggestions based on query input (incremental search)
     */
    public function autocomplete(Request $request)
    {
        $input = $request->query('query');
        if (empty($input)) {
            return response()->json([]);
        }

        $googleKey = config('services.google.maps_api_key');
        if (!empty($googleKey)) {
            try {
                $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                    'input' => $input,
                    'key' => $googleKey,
                    'language' => 'es',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'OK' && !empty($data['predictions'])) {
                        $suggestions = array_map(function ($prediction) {
                            return $prediction['description'];
                        }, $data['predictions']);
                        
                        return response()->json($suggestions);
                    } else {
                        Log::warning('Google Places Autocomplete status was not OK: ' . ($data['status'] ?? 'unknown'));
                    }
                } else {
                    Log::error('Google Places Autocomplete HTTP request failed.', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Autocomplete Google Places API exception: ' . $e->getMessage());
            }
        }

        // Fallback to Gemini if Google Maps key is missing or fails
        return $this->autocompleteViaGemini($input);
    }

    /**
     * Fallback autocomplete using Gemini API to suggest realistic addresses.
     */
    private function autocompleteViaGemini(string $input): \Illuminate\Http\JsonResponse
    {
        $apiKey = config('gemini.api_key');
        $model = config('gemini.model', 'gemini-3.5-flash-lite-preview');
        
        if (empty($apiKey)) {
            Log::warning('Gemini API key is not configured for autocomplete fallback.');
            return response()->json([
                $input . ', Ciudad de México, CDMX, México',
                $input . ', Guadalajara, Jalisco, México',
                $input . ', Monterrey, Nuevo León, México',
            ]);
        }

        $prompt = "Actúa como un servicio de autocompletado de direcciones. El usuario está escribiendo la siguiente dirección parcial: \"{$input}\".
        Genera una lista de 5 direcciones reales o sumamente realistas en México que comiencen o contengan esta cadena.
        Debes retornar estrictamente un arreglo JSON de strings (ej. [\"Dirección 1\", \"Dirección 2\"]).
        No incluyas explicaciones, no incluyas marcas de markdown de bloque de código, solo el arreglo JSON crudo.";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        try {
            $response = Http::timeout(5)->post($url, [
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
                $textResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
                
                $cleanJson = trim($textResponse);
                if (str_starts_with($cleanJson, '```')) {
                    $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
                    $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);
                }
                
                $parsed = json_decode($cleanJson, true);
                if (is_array($parsed)) {
                    return response()->json(array_slice($parsed, 0, 5));
                }
            }
            
            Log::error('Gemini API returned an invalid response format or failed for autocomplete.', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('Autocomplete Gemini fallback exception: ' . $e->getMessage());
        }

        // Final fallback if both fail
        return response()->json([
            $input . ', Ciudad de México, CDMX, México',
            $input . ', Monterrey, Nuevo León, México',
            $input . ', Guadalajara, Jalisco, México',
        ]);
    }
}

