<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización - Cliente</title>
    <style>
        body {font-family: 'Outfit', sans-serif; background-color: #020617; color: #f8fafc; padding: 30px;}
        .container {max-width: 800px; margin: auto; background: rgba(15,23,42,0.7); padding: 20px; border-radius: 12px;}
        h1 {color: #ed3426;}
        table {width: 100%; border-collapse: collapse; margin-top: 20px;}
        th, td {border: 1px solid #444; padding: 8px; text-align: left;}
        th {background: #1e293b;}
        .footer {margin-top: 30px; font-size: 0.9em; color: #a1a1aa;}
    </style>
</head>
<body>
<div class="container">
    <h1>Cotización para {{ $quote->nombre_cliente }}</h1>
    <p><strong>Correo:</strong> {{ $quote->email_cliente }}</p>
    @if($quote->telefono_cliente)
        <p><strong>Teléfono:</strong> {{ $quote->telefono_cliente }}</p>
    @endif
    <p><strong>Origen:</strong> {{ $quote->origen }}</p>
    <p><strong>Destino:</strong> {{ $quote->destino }}</p>
    <p><strong>Volumen total:</strong> {{ number_format($quote->volumen_total_m3, 2) }} m³</p>
    <p><strong>Precio sugerido:</strong> ${{ number_format($quote->precio_sugerido, 2) }}</p>
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
