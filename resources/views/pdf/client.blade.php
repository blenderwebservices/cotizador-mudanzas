<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización - Cliente</title>
    <style>
        body {font-family: 'Outfit', sans-serif; background-color: #ffffff; color: #1e293b; padding: 30px;}
        .container {max-width: 800px; margin: auto; background: #ffffff; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;}
        h1 {color: #ed3426;}
        table {width: 100%; border-collapse: collapse; margin-top: 20px; color: #1e293b;}
        th, td {border: 1px solid #cbd5e1; padding: 10px; text-align: left;}
        th {background: #f1f5f9; color: #0f172a; font-weight: bold;}
        .footer {margin-top: 30px; font-size: 0.9em; color: #64748b;}
    </style>
</head>
<body>
<div class="container">
    <h1>Cotización para {{ $quote->nombre_cliente }}</h1>
    <p><strong>Correo:</strong> {{ $quote->email_cliente }}</p>
    @if($quote->telefono_cliente)
        <p><strong>Teléfono:</strong> {{ $quote->telefono_cliente }}</p>
    @endif
    <p><strong>Origen:</strong> {{ $quote->origen }} (Piso {{ $quote->pisos_origen }})</p>
    <p><strong>Destino:</strong> {{ $quote->destino }} (Piso {{ $quote->pisos_destino }})</p>
    <p><strong>Distancia estimada:</strong> {{ number_format($quote->distancia_km, 1) }} km</p>
    <p><strong>Volumen total:</strong> {{ number_format($quote->volumen_total_m3, 2) }} m³</p>
    <p><strong>Precio del servicio:</strong> ${{ number_format($quote->precio_sugerido, 2) }}</p>
    <h2>Items seleccionados</h2>
    <table>
        <thead>
            <tr><th>Item</th><th>Cantidad</th><th>Volumen (m³)</th><th>Peso (kg)</th></tr>
        </thead>
        <tbody>
        @foreach($items as $qi)
            <tr>
                <td>{{ $qi->item->nombre }}</td>
                <td>{{ $qi->cantidad }}</td>
                <td>{{ $qi->volumen_m3 ?? '-' }}</td>
                <td>{{ $qi->peso_kg ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="footer">
        <p>Gracias por confiar en Mudanzas Hermanos Monroy.</p>
    </div>
</div>
</body>
</html>
