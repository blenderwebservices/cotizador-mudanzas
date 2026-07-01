<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\QuoteCalculator;
use App\Models\Vehicle;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class QuoteCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected QuoteCalculator $calculator;
    protected Vehicle $vehicle;
    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->calculator = new QuoteCalculator();

        // Seed basic vehicle and item needed by the calculator
        $this->vehicle = Vehicle::create([
            'nombre' => 'Vehículo de Prueba',
            'capacidad_m3' => 50.00,
            'consumo_kml' => 10.00,
            'activo' => true,
        ]);

        $this->item = Item::create([
            'nombre' => 'Mesa',
            'categoria' => 'Sala',
            'grupo_categoria' => 'Muebles',
            'cantidad' => 1,
            'costo_empaque' => 50.00,
            'tiempo_empaque' => 10,
            'tamano_volumetrico' => 1.5,
            'nivel_riesgo' => 'bajo',
            'requiere_desarmarse' => false,
            'activo' => true,
            'permite_detalles_opcionales' => false,
        ]);

        // Default app configuration needed by the calculator
        Config::set('mudanzas.precio_combustible_por_litro', 24.50);
        Config::set('mudanzas.precio_comida_por_persona', 150.00);
        Config::set('mudanzas.salario_por_hora_por_persona', 150.00);
        Config::set('mudanzas.tarifa_minima', 6500.00);
        Config::set('mudanzas.ganancia_porcentaje', 0.50);
        Config::set('mudanzas.factor_tiempo_carga_por_m3', 0.1);
        Config::set('mudanzas.abc.tarifa_comercial_fija', 150.00);
        Config::set('mudanzas.abc.costo_carga_base_m3', 60.00);
        Config::set('mudanzas.abc.costo_descarga_base_m3', 60.00);
        Config::set('mudanzas.abc.tarifa_escalera_m3_piso', 25.00);
        Config::set('mudanzas.abc.tarifa_caminata_m3_metro', 1.50);
        Config::set('mudanzas.abc.desarme_tiempo_minutos', 15);
        Config::set('mudanzas.abc.costo_por_km_vehiculo.' . $this->vehicle->id, 6.00);
    }

    public function test_uses_google_maps_api_when_key_is_provided()
    {
        Config::set('services.google.maps_api_key', 'mock-google-maps-key');

        Http::fake([
            'maps.googleapis.com/maps/api/distancematrix/json*' => Http::response([
                'status' => 'OK',
                'origin_addresses' => ['Origen Address'],
                'destination_addresses' => ['Destino Address'],
                'rows' => [
                    [
                        'elements' => [
                            [
                                'status' => 'OK',
                                'distance' => [
                                    'value' => 25300, // 25.3 km
                                    'text' => '25.3 km'
                                ],
                                'duration' => [
                                    'value' => 1800, // 30 mins / 0.5 hours
                                    'text' => '30 mins'
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $clientData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'origin' => 'Origen',
            'destination' => 'Destino',
            'elevatorStart' => 'no',
            'pisos_origen' => 1,
            'distancia_caminata_origen_m' => 10,
            'pisos_destino' => 1,
            'ascensor_destino' => 'yes',
            'distancia_caminata_destino_m' => 10,
        ];

        $selectedItems = [
            [
                'item_id' => $this->item->id,
                'count' => 1
            ]
        ];

        $results = $this->calculator->calculate($clientData, $selectedItems);

        $this->assertEquals(25.3, $results['distancia_km']);
        $this->assertEquals(0.5, $results['tiempo_traslado_horas']);
        
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'maps.googleapis.com') && 
                   $request['key'] === 'mock-google-maps-key' &&
                   $request['origins'] === 'Origen' &&
                   $request['destinations'] === 'Destino';
        });

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });
    }

    public function test_falls_back_to_gemini_when_google_maps_key_is_missing()
    {
        Config::set('services.google.maps_api_key', null);
        Config::set('gemini.api_key', 'mock-gemini-key');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '{"distancia_km": 18.5, "tiempo_traslado_horas": 1.2}']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $clientData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'origin' => 'Origen',
            'destination' => 'Destino',
            'elevatorStart' => 'no',
        ];

        $selectedItems = [
            [
                'item_id' => $this->item->id,
                'count' => 1
            ]
        ];

        $results = $this->calculator->calculate($clientData, $selectedItems);

        $this->assertEquals(18.5, $results['distancia_km']);
        $this->assertEquals(1.2, $results['tiempo_traslado_horas']);

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), 'maps.googleapis.com');
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });
    }

    public function test_falls_back_to_gemini_when_google_maps_api_fails()
    {
        Config::set('services.google.maps_api_key', 'mock-google-maps-key');
        Config::set('gemini.api_key', 'mock-gemini-key');

        Http::fake([
            'maps.googleapis.com/*' => Http::response(['status' => 'REQUEST_DENIED'], 200),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '{"distancia_km": 12.0, "tiempo_traslado_horas": 0.8}']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $clientData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'origin' => 'Origen',
            'destination' => 'Destino',
            'elevatorStart' => 'no',
        ];

        $selectedItems = [
            [
                'item_id' => $this->item->id,
                'count' => 1
            ]
        ];

        $results = $this->calculator->calculate($clientData, $selectedItems);

        $this->assertEquals(12.0, $results['distancia_km']);
        $this->assertEquals(0.8, $results['tiempo_traslado_horas']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'maps.googleapis.com');
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });
    }

    public function test_falls_back_to_default_estimation_when_both_apis_fail()
    {
        Config::set('services.google.maps_api_key', 'mock-google-maps-key');
        Config::set('gemini.api_key', 'mock-gemini-key');

        Http::fake([
            'maps.googleapis.com/*' => Http::response([], 500),
            'generativelanguage.googleapis.com/*' => Http::response([], 500)
        ]);

        $clientData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'origin' => 'Origen',
            'destination' => 'Destino',
            'elevatorStart' => 'no',
        ];

        $selectedItems = [
            [
                'item_id' => $this->item->id,
                'count' => 1
            ]
        ];

        $results = $this->calculator->calculate($clientData, $selectedItems);

        // Fallback default estimation is 15.00 km and 1.00 hour
        $this->assertEquals(15.00, $results['distancia_km']);
        $this->assertEquals(1.00, $results['tiempo_traslado_horas']);
    }
}
