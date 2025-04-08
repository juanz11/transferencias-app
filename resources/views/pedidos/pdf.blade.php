<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Pedidos</title>
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
        h4 {
            margin-top: 20px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h2>Reporte de Pedidos - {{ $tipoVista === 'agrupado' ? 'Vista Agrupada' : 'Vista Individual' }}</h2>
    
    <!-- Tabla principal -->
    <table class="table">
        <thead>
            <tr>
                <th>Fecha Transferencia</th>
                <th>Fecha Confirmación</th>
                <th>Visitador</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Descuento</th>
                <th>N° Transferencia</th>
            </tr>
        </thead>
        <tbody>
            @if($tipoVista === 'agrupado')
                @foreach($pedidosAgrupados as $pedido)
                    <tr>
                        <td>{{ $pedido['fecha_transferencia']->format('d/m/Y') }}</td>
                        <td>{{ $pedido['fecha_confirmacion']->format('d/m/Y') }}</td>
                        <td>{{ $pedido['visitador'] }}</td>
                        <td>{{ $pedido['producto'] }}</td>
                        <td><strong>{{ $pedido['cantidad'] }}</strong></td>
                        <td>{{ $pedido['descuento'] }}%</td>
                        <td>{{ $pedido['transferencias'] }}</td>
                    </tr>
                @endforeach
            @else
                @foreach($pedidos as $pedido)
                    <tr>
                        <td>{{ $pedido->transferenciaConfirmada->transferencia->fecha_transferencia->format('d/m/Y') }}</td>
                        <td>{{ $pedido->transferenciaConfirmada->created_at->format('d/m/Y') }}</td>
                        <td>{{ $pedido->transferenciaConfirmada->transferencia->visitador->nombre }}</td>
                        <td>{{ $pedido->producto->nombre }}</td>
                        <td>{{ $pedido->cantidad }}</td>
                        <td>{{ $pedido->descuento }}%</td>
                        <td>{{ $pedido->transferenciaConfirmada->transferencia->transferencia_numero }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- Resumen por Visitador -->
    <h4>Resumen por Visitador</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Visitador</th>
                <th>Producto</th>
                <th>Cantidad Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resumenVisitador as $grupo)
                @foreach($grupo['productos'] as $index => $producto)
                    <tr>
                        @if($index === 0)
                            <td rowspan="{{ count($grupo['productos']) }}">{{ $grupo['visitador'] }}</td>
                        @endif
                        <td>{{ $producto['producto'] }}</td>
                        <td>{{ $producto['cantidad'] }}</td>
                    </tr>
                @endforeach
                <tr class="table-secondary">
                    <td colspan="2" class="text-end"><strong>Total del Visitador:</strong></td>
                    <td><strong>{{ $grupo['total_visitador'] }}</strong></td>
                </tr>
            @endforeach
            <tr class="table-dark">
                <td colspan="2" class="text-end"><strong>Total General:</strong></td>
                <td><strong>{{ $totalProductos }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
