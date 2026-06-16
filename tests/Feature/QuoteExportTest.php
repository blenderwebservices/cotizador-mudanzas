<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Quote;
use App\Models\Vehicle;
use App\Models\Agent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuoteExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_download_client_pdf_in_light_theme()
    {
        $vehicle = Vehicle::create([
            'nombre' => 'Vehículo Ligero (18 m³)',
            'capacidad_m3' => 18.00,
            'consumo_kml' => 8.00,
            'activo' => true,
        ]);

        $agent = Agent::create([
            'name' => 'Carlos Mendoza',
            'email' => 'carlos@mudanzashnosmonroy.com',
            'phone' => '555-0199',
            'status' => 'active',
        ]);

        $quote = Quote::create([
            'nombre_cliente' => 'Test Cliente',
            'email_cliente' => 'test@cliente.com',
            'telefono_cliente' => '1234567890',
            'origen' => 'Calle Origen 123',
            'destino' => 'Calle Destino 456',
            'pisos_origen' => 1,
            'distancia_caminata_origen_m' => 10,
            'pisos_destino' => 1,
            'ascensor_destino' => true,
            'distancia_caminata_destino_m' => 10,
            'costo_actividad_comercial' => 100,
            'costo_actividad_embalaje' => 200,
            'costo_actividad_carga' => 150,
            'costo_actividad_transporte' => 300,
            'costo_actividad_descarga' => 150,
            'distancia_km' => 10.5,
            'tiempo_traslado_horas' => 0.5,
            'volumen_total_m3' => 5.0,
            'costo_empaque_total' => 100,
            'vehiculo_sugerido_id' => $vehicle->id,
            'personas_sugeridas' => 2,
            'combustible_l' => 2,
            'costo_combustible' => 40,
            'tiempo_empaque_total_min' => 45,
            'tiempo_carga_horas' => 1.0,
            'material_empaque_costo' => 50,
            'comida_trabajadores_costo' => 100,
            'salarios_costo' => 200,
            'gastos_totales' => 800,
            'ganancia_estimada' => 400,
            'precio_sugerido' => 1200,
            'agent_id' => $agent->id,
            'detalles_json' => ['elevatorStart' => 'yes'],
        ]);

        $response = $this->get(route('quotes.pdf.client', ['quoteId' => $quote->id]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_download_admin_excel()
    {
        $vehicle = Vehicle::create([
            'nombre' => 'Vehículo Ligero (18 m³)',
            'capacidad_m3' => 18.00,
            'consumo_kml' => 8.00,
            'activo' => true,
        ]);

        $agent = Agent::create([
            'name' => 'Carlos Mendoza',
            'email' => 'carlos@mudanzashnosmonroy.com',
            'phone' => '555-0199',
            'status' => 'active',
        ]);

        $quote = Quote::create([
            'nombre_cliente' => 'Test Cliente',
            'email_cliente' => 'test@cliente.com',
            'telefono_cliente' => '1234567890',
            'origen' => 'Calle Origen 123',
            'destino' => 'Calle Destino 456',
            'pisos_origen' => 1,
            'distancia_caminata_origen_m' => 10,
            'pisos_destino' => 1,
            'ascensor_destino' => true,
            'distancia_caminata_destino_m' => 10,
            'costo_actividad_comercial' => 100,
            'costo_actividad_embalaje' => 200,
            'costo_actividad_carga' => 150,
            'costo_actividad_transporte' => 300,
            'costo_actividad_descarga' => 150,
            'distancia_km' => 10.5,
            'tiempo_traslado_horas' => 0.5,
            'volumen_total_m3' => 5.0,
            'costo_empaque_total' => 100,
            'vehiculo_sugerido_id' => $vehicle->id,
            'personas_sugeridas' => 2,
            'combustible_l' => 2,
            'costo_combustible' => 40,
            'tiempo_empaque_total_min' => 45,
            'tiempo_carga_horas' => 1.0,
            'material_empaque_costo' => 50,
            'comida_trabajadores_costo' => 100,
            'salarios_costo' => 200,
            'gastos_totales' => 800,
            'ganancia_estimada' => 400,
            'precio_sugerido' => 1200,
            'agent_id' => $agent->id,
            'detalles_json' => ['elevatorStart' => 'yes'],
        ]);

        $response = $this->get(route('quotes.excel.admin', ['quoteId' => $quote->id]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('attachment; filename="cotizacion_admin_', $response->headers->get('Content-Disposition'));
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
