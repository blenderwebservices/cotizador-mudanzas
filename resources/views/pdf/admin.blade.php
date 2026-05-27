<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización - Administrador</title>
    <style>
        body {font-family: 'Outfit', sans-serif; background-color: #020617; color: #f8fafc; padding: 30px;}
        .container {max-width: 900px; margin: auto; background: rgba(15,23,42,0.7); padding: 20px; border-radius: 12px;}
        h1, h2 {color: #ed3426;}
        table {width: 100%; border-collapse: collapse; margin-top: 20px;}
        th, td {border: 1px solid #444; padding: 8px; text-align: left;}
        th {background: #1e293b;}
        .section {margin-top: 20px;}
        .footer {margin-top: 30px; font-size: 0.9em; color: #a1a1aa;}
    </style>
</head>
<body>
<div class="container">
    <h1>Cotización - Detalle Administrador</h1>
    <p><strong>Cliente:</strong> {{ $quote->nombre_cliente }}</p>
    <p><strong>Correo:</strong> {{ $quote->email_cliente }}</p>
    @if($quote->telefono_cliente)
        <p><strong>Teléfono:</strong> {{ $quote->telefono_cliente }}</p>
    @endif
    <p><strong>Origen:</strong> {{ $quote->origen }}</p>
    <p><strong>Destino:</strong> {{ $quote->destino }}</p>
    <div class="section">
        <h2>Resumen de la Cotización</h2>
        <table>
            <tbody>
                <tr><th>Volumen Total (m³)</th><td>{{ number_format($quote->volumen_total_m3, 2) }}</td></tr>
                <tr><th>Distancia Estimada (km)</th><td>{{ number_format($quote->distancia_km, 2) }}</td></tr>
                <tr><th>Vehículo Sugerido</th><td>{{ $quote->vehiculo_sugerido_id }}</td></tr>
                <tr><th>Personal Sugerido</th><td>{{ $quote->personas_sugeridas }}</td></tr>
                <tr><th>Tiempo de Traslado (h)</th><td>{{ $quote->tiempo_traslado_horas }}</td></tr>
                <tr><th>Tiempo de Empaque (min)</th><td>{{ $quote->tiempo_empaque_total_min }}</td></tr>
                <tr><th>Costo de Empaque Total</th><td>${{ number_format($quote->costo_empaque_total, 2) }}</td></tr>
                <tr><th>Combustible (L)</th><td>{{ number_format($quote->combustible_l, 2) }}</td></tr>
                <tr><th>Costo Combustible</th><td>${{ number_format($quote->costo_combustible, 2) }}</td></tr>
                <tr><th>Material de Empaque</th><td>${{ number_format($quote->material_empaque_costo, 2) }}</td></tr>
                <tr><th>Comida Trabajadores</th><td>${{ number_format($quote->comida_trabajadores_costo, 2) }}</td></tr>
                <tr><th>Salarios</th><td>${{ number_format($quote->salarios_costo, 2) }}</td></tr>
                <tr><th>Gastos Totales</th><td>${{ number_format($quote->gastos_totales, 2) }}</td></tr>
                <tr><th>Ganancia Estimada</th><td>${{ number_format($quote->ganancia_estimada, 2) }}</td></tr>
                <tr><th>Precio Sugerido</th><td>${{ number_format($quote->precio_sugerido, 2) }}</td></tr>
                <tr><th>Ganancia Sugerida</th><td>${{ number_format($quote->ganancia_estimada, 2) }}</td></tr>
            </tbody>
        </table>
    </div>
    <div class="section">
        <h2>Items Seleccionados</h2>
        <table>
            <thead>
                <tr><th>Item</th><th>Cantidad</th><th>Volumen (m³)</th><th>Peso (kg)</th><th>Costo Empaque</th></tr>
            </thead>
            <tbody>
                @foreach($items as $qi)
                    <tr>
                        <td>{{ $qi->item->nombre }}</td>
                        <td>{{ $qi->cantidad }}</td>
                        <td>{{ $qi->volumen_m3 ?? '-' }}</td>
                        <td>{{ $qi->peso_kg ?? '-' }}</td>
                        <td>${{ number_format($qi->item->costo_empaque ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="footer">
        <p>Generado por Mudanzas Hermanos Monroy - {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>
</body>
</html>
