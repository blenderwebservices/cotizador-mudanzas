<?php

return [
    'precio_combustible_por_litro' => 24.50, // MXN per liter
    'precio_comida_por_persona' => 150.00, // MXN per meal per worker
    'salario_por_hora_por_persona' => 150.00, // MXN per hour per worker
    'tarifa_minima' => 6500.00, // Minimum suggested tariff
    'ganancia_porcentaje' => 0.50, // 50% target profit margin
    'factor_tiempo_carga_por_m3' => 0.1, // 0.1 hours (6 mins) loading time per m3 of volume

    // Parámetros del modelo ABC (Activity-Based Costing)
    'abc' => [
        'tarifa_comercial_fija' => 150.00,    // Tarifa fija de cotización y administración (Actividad A)
        'costo_carga_base_m3' => 60.00,       // Costo base de cargar 1 m³ (Actividad C)
        'costo_descarga_base_m3' => 60.00,    // Costo base de descargar 1 m³ (Actividad E)
        'tarifa_escalera_m3_piso' => 25.00,    // Recargo por piso extra sin ascensor por m³
        'tarifa_caminata_m3_metro' => 1.50,    // Recargo por metro de caminata adicional (más allá de 10m) por m³
        'desarme_tiempo_minutos' => 15,        // Tiempo extra estimado de desarme por mueble (Actividad B)
        'vehiculo_depreciacion_km' => [
            1 => 6.00,  // Vehículo ligero ($ / Km de desgaste/seguro/mantenimiento)
            2 => 10.00, // Vehículo mediano ($ / Km)
            3 => 15.00, // Vehículo grande ($ / Km)
        ]
    ]
];
