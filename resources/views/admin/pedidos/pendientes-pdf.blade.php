<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pedidos Pendientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #1f4591;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #1f4591;
        }
        .header p {
            margin: 3px 0;
            font-size: 11px;
        }
        .filtros {
            margin-bottom: 12px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #999;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #1f4591;
            color: #fff;
            font-size: 10px;
        }
        td ul {
            margin: 0;
            padding-left: 15px;
        }
        .totales {
            margin-top: 15px;
        }
        .totales h2 {
            font-size: 14px;
            color: #1f4591;
            margin-bottom: 8px;
        }
        .totales table {
            width: 60%;
        }
        .totales th {
            background-color: #444;
        }
        .resumen {
            margin-top: 10px;
            font-size: 12px;
        }
        .resumen strong {
            color: #1f4591;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Pedidos Pendientes</h1>
        <p>Generado el {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="filtros">
        <strong>Droguería:</strong> {{ $drogueriaNombre }} &nbsp;|&nbsp;
        <strong>Zona:</strong> {{ $zonaNombre }}
    </div>

    <table>
        <thead>
            <tr>
                <th>N° Transf.</th>
                <th>Fecha</th>
                <th>Visitador</th>
                <th>Cliente</th>
                <th>Droguería</th>
                <th>Productos pendientes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transferencias as $transferencia)
                <tr>
                    <td>{{ $transferencia->transferencia_numero }}</td>
                    <td>{{ optional($transferencia->fecha_transferencia)->format('d/m/Y') }}</td>
                    <td>{{ optional($transferencia->visitador)->nombre }}</td>
                    <td>{{ optional($transferencia->cliente)->nombre_cliente }}</td>
                    <td>{{ $transferencia->drogueria_nombre ?? '' }}</td>
                    <td>
                        <ul>
                            @foreach($transferencia->pedidos->where('estado', 'pendiente') as $pedido)
                                <li>{{ $pedido->producto->nombre }}: {{ $pedido->cantidad }} unds ({{ $pedido->descuento }}% desc)</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">No hay pedidos pendientes.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totales">
        <h2>Totales por Producto</h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($totalesPorProducto as $producto => $cantidad)
                    <tr>
                        <td>{{ $producto }}</td>
                        <td>{{ $cantidad }} unds</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style="text-align:center;">Sin datos</td>
                    </tr>
                @endforelse
                <tr>
                    <th>Total General</th>
                    <th>{{ $totalUnidades }} unds</th>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="resumen">
        <p><strong>Total de transferencias con pedidos pendientes:</strong> {{ $totalTransferencias }}</p>
        <p><strong>Total de unidades pendientes:</strong> {{ $totalUnidades }}</p>
    </div>

    <div class="footer">
        <p>Reporte generado automáticamente - {{ config('app.name') }}</p>
    </div>
</body>
</html>
