<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resumen por Visitador</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        .table th, .table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .table-secondary {
            background-color: #e2e3e5;
        }
        .table-dark {
            background-color: #343a40;
            color: white;
        }
        .text-end {
            text-align: right;
        }
        h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h3>Resumen por Visitador</h3>
    @if(!empty($fechaInicio) && !empty($fechaFin))
        <p>Período: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
    @endif
    <table class="table">
        <thead>
            <tr>
                <th>Visitador</th>
                <th>Producto</th>
                <th>Cantidad Total</th>
                <th>Ganancia Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $gananciaTotal = 0;
            @endphp
            @foreach($resumenVisitador as $grupo)
                @php
                    $gananciaVisitador = 0;
                @endphp
                @foreach($grupo['productos'] as $index => $producto)
                    @php
                        $productoModel = \App\Models\Producto::where('nombre', $producto['producto'])->first();
                        $ganancia = $productoModel ? $producto['cantidad'] * $productoModel->comision : 0;
                        $gananciaVisitador += $ganancia;
                        $gananciaTotal += $ganancia;
                    @endphp
                    <tr>
                        @if($index === 0)
                            <td rowspan="{{ count($grupo['productos']) }}">{{ $grupo['visitador'] }}</td>
                        @endif
                        <td>{{ $producto['producto'] }}</td>
                        <td>{{ $producto['cantidad'] }}</td>
                        <td>${{ number_format($ganancia, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="table-secondary">
                    <td colspan="2" class="text-end"><strong>Total del Visitador:</strong></td>
                    <td><strong>{{ $grupo['total_visitador'] }}</strong></td>
                    <td><strong>${{ number_format($gananciaVisitador, 2) }}</strong></td>
                </tr>
            @endforeach
            <tr class="table-dark">
                <td colspan="2" class="text-end"><strong>Total General:</strong></td>
                <td><strong>{{ $totalProductos }}</strong></td>
                <td><strong>${{ number_format($gananciaTotal, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
