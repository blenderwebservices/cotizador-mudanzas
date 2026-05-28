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
        <h2>Drivers de Complejidad Logística</h2>
        <table>
            <tbody>
                <tr><th>Origen</th><td>Piso {{ $quote->pisos_origen }} · Ascensor: {{ ($quote->detalles_json['elevatorStart'] ?? 'no') === 'yes' ? 'Sí' : 'No' }} · Caminata: {{ $quote->distancia_caminata_origen_m }} m</td></tr>
                <tr><th>Destino</th><td>Piso {{ $quote->pisos_destino }} · Ascensor: {{ $quote->ascensor_destino ? 'Sí' : 'No' }} · Caminata: {{ $quote->distancia_caminata_destino_m }} m</td></tr>
                <tr><th>Vehículo y Personal</th><td>{{ $quote->vehiculoSugerido ? $quote->vehiculoSugerido->nombre : 'N/A' }} · {{ $quote->personas_sugeridas }} cargadores</td></tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Desglose de Actividades (Modelo ABC)</h2>
        <table>
            <tbody>
                <tr><th>Actividad A: Comercial y Planificación</th><td>${{ number_format($quote->costo_actividad_comercial, 2) }}</td></tr>
                <tr><th>Actividad B: Embalaje y Preparación</th><td>${{ number_format($quote->costo_actividad_embalaje, 2) }}</td></tr>
                <tr><th>Actividad C: Carga y Estiba</th><td>${{ number_format($quote->costo_actividad_carga, 2) }}</td></tr>
                <tr><th>Actividad D: Transporte (Conducción)</th><td>${{ number_format($quote->costo_actividad_transporte, 2) }}</td></tr>
                <tr><th>Actividad E: Descarga y Desembalaje</th><td>${{ number_format($quote->costo_actividad_descarga, 2) }}</td></tr>
                <tr style="background: #1e293b; font-weight: bold;"><th>Costo Operativo Total (Gastos)</th><td>${{ number_format($quote->gastos_totales, 2) }}</td></tr>
                <tr style="font-weight: bold; color: #10b981;"><th>Ganancia Estimada</th><td>${{ number_format($quote->ganancia_estimada, 2) }}</td></tr>
                <tr style="background: #ed3426; color: #fff; font-weight: bold;"><th>PRECIO SUGERIDO AL CLIENTE</th><td>${{ number_format($quote->precio_sugerido, 2) }}</td></tr>
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
