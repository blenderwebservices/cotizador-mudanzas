<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Agent;
use App\Models\Item;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Admin User for Filament
        User::updateOrCreate(
            ['email' => 'admin@mudanzashnosmonroy.com'],
            [
                'name' => 'Administrador Mudanzas Monroy',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Seed Agents
        $agents = [
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos@mudanzashnosmonroy.com',
                'phone' => '555-0199',
                'status' => 'active',
            ],
            [
                'name' => 'Dulce Monroy',
                'email' => 'dulce@mudanzashnosmonroy.com',
                'phone' => '555-0211',
                'status' => 'active',
            ],
            [
                'name' => 'Diego Monroy',
                'email' => 'diego@mudanzashnosmonroy.com',
                'phone' => '555-0344',
                'status' => 'inactive',
            ],
        ];

        foreach ($agents as $agent) {
            Agent::updateOrCreate(['email' => $agent['email']], $agent);
        }

        // 3. Seed Vehicles (18, 35, 80 cubic meters capacity)
        $vehicles = [
            [
                'nombre' => 'Vehículo Ligero (18 m³)',
                'capacidad_m3' => 18.00,
                'consumo_kml' => 8.00, // 8 km per liter
                'activo' => true,
            ],
            [
                'nombre' => 'Vehículo Mediano (35 m³)',
                'capacidad_m3' => 35.00,
                'consumo_kml' => 5.50, // 5.5 km per liter
                'activo' => true,
            ],
            [
                'nombre' => 'Vehículo Grande (80 m³)',
                'capacidad_m3' => 80.00,
                'consumo_kml' => 3.50, // 3.5 km per liter
                'activo' => true,
            ],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::updateOrCreate(['nombre' => $vehicle['nombre']], $vehicle);
        }

        // 4. Seed Inventory Items
        $items = [
            [
                'nombre' => 'Cama King',
                'cantidad' => 1,
                'costo_empaque' => 450.00,
                'tiempo_empaque' => 30, // 30 minutes
                'tamano_volumetrico' => 3.000, // 3 m³
                'nivel_riesgo' => 'bajo',
                'requiere_desarmarse' => true,
                'activo' => true,
                'permite_detalles_opcionales' => false,
                'icon' => '🛏️',
            ],
            [
                'nombre' => 'Cama 1 Plaza',
                'cantidad' => 1,
                'costo_empaque' => 250.00,
                'tiempo_empaque' => 20, // 20 minutes
                'tamano_volumetrico' => 1.500, // 1.5 m³
                'nivel_riesgo' => 'bajo',
                'requiere_desarmarse' => true,
                'activo' => true,
                'permite_detalles_opcionales' => false,
                'icon' => '卧',
            ],
            [
                'nombre' => 'Sofá 3 Cuerpos',
                'cantidad' => 1,
                'costo_empaque' => 350.00,
                'tiempo_empaque' => 15,
                'tamano_volumetrico' => 2.500,
                'nivel_riesgo' => 'medio',
                'requiere_desarmarse' => false,
                'activo' => true,
                'permite_detalles_opcionales' => false,
                'icon' => '🛋️',
            ],
            [
                'nombre' => 'Televisor',
                'cantidad' => 1,
                'costo_empaque' => 300.00,
                'tiempo_empaque' => 15,
                'tamano_volumetrico' => 0.800,
                'nivel_riesgo' => 'alto',
                'requiere_desarmarse' => false,
                'activo' => true,
                'permite_detalles_opcionales' => true, // custom size & weight allowed
                'icon' => '📺',
            ],
            [
                'nombre' => 'Refrigerador',
                'cantidad' => 1,
                'costo_empaque' => 250.00,
                'tiempo_empaque' => 10,
                'tamano_volumetrico' => 2.200,
                'nivel_riesgo' => 'medio',
                'requiere_desarmarse' => false,
                'activo' => true,
                'permite_detalles_opcionales' => true, // custom size & weight allowed
                'icon' => '🧊',
            ],
            [
                'nombre' => 'Lavadora',
                'cantidad' => 1,
                'costo_empaque' => 150.00,
                'tiempo_empaque' => 10,
                'tamano_volumetrico' => 1.200,
                'nivel_riesgo' => 'medio',
                'requiere_desarmarse' => false,
                'activo' => true,
                'permite_detalles_opcionales' => false,
                'icon' => '🧺',
            ],
            [
                'nombre' => 'Mesa Comedor',
                'cantidad' => 1,
                'costo_empaque' => 200.00,
                'tiempo_empaque' => 25,
                'tamano_volumetrico' => 2.000,
                'nivel_riesgo' => 'bajo',
                'requiere_desarmarse' => true,
                'activo' => true,
                'permite_detalles_opcionales' => false,
                'icon' => '🪑',
            ],
            [
                'nombre' => 'Cajas (x5)',
                'cantidad' => 1,
                'costo_empaque' => 250.00,
                'tiempo_empaque' => 15,
                'tamano_volumetrico' => 0.500,
                'nivel_riesgo' => 'bajo',
                'requiere_desarmarse' => false,
                'activo' => true,
                'permite_detalles_opcionales' => false,
                'icon' => '📦',
            ],
        ];

        // Fix Cama 1 Plaza icon which got a chinese character from prompt backup or was🛌
        // Let's use 🛌 for Cama 1 Plaza
        foreach ($items as &$it) {
            if ($it['nombre'] === 'Cama 1 Plaza') {
                $it['icon'] = '🛌';
            }
        }

        foreach ($items as $item) {
            Item::updateOrCreate(['nombre' => $item['nombre']], $item);
        }
    }
}
