<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Agent;
use App\Models\Item;
use App\Models\Vehicle;
use App\Models\Category;
use App\Models\GroupCategory;
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

        // 4. Seed Inventory Items from JSON file
        $jsonPath = base_path('assets/productos/items.json');
        if (file_exists($jsonPath)) {
            $items = json_decode(file_get_contents($jsonPath), true);
        } else {
            $items = [];
        }

        // Seed unique categories
        $categories = collect($items)->pluck('categoria')->filter()->unique();
        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat]);
        }

        // Seed unique group categories
        $groups = collect($items)->pluck('grupo_categoria')->filter()->unique();
        foreach ($groups as $grp) {
            GroupCategory::firstOrCreate(['name' => $grp]);
        }

        foreach ($items as $item) {
            Item::updateOrCreate(['nombre' => $item['nombre']], $item);
        }
    }
}
