<?php
// Backward‑compatible configuration file for the Mudanzas cotizador.
// This mirrors the values defined in config/mudanzas.php so legacy code
// that includes `config.php` continues to function.

return [
    'precio_combustible_por_litro' => 24.50, // MXN per liter
    'precio_comida_por_persona'    => 150.00, // MXN per meal per worker
    'salario_por_hora_por_persona' => 150.00, // MXN per hour per worker
    'tarifa_minima'                => 6500.00, // Minimum suggested tariff
    'ganancia_porcentaje'          => 0.50,   // 50% target profit margin
    'factor_tiempo_carga_por_m3'   => 0.1,    // 0.1 hours (6 mins) loading time per m³
];
