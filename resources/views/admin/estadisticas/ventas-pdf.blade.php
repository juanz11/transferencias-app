<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas de Ventas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .resumen {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
        }
        .resumen-item {
            text-align: center;
        }
        .resumen-item h3 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .resumen-item p {
            margin: 5px 0 0;
            color: #666;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #444;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tfoot tr {
            background-color: #444;
            color: white;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Estadísticas de Ventas</h1>
        <p>Reporte generado el {{ date('d/m/Y H:i') }}</p>
        @if($fechaInicio && $fechaFin)
            <p>Período: {{ date('d/m/Y', strtotime($fechaInicio)) }} - {{ date('d/m/Y', strtotime($fechaFin)) }}</p>
        @endif
        <p>Visitador: {{ $visitadorNombre }}</p>
    </div>

    <div class="resumen">
        <div class="resumen-item">
            <h3>{{ number_format($totalTransferencias) }}</h3>
            <p>Total Transferencias</p>
        </div>
        <div class="resumen-item">
            <h3>{{ number_format($totalUnidades) }}</h3>
            <p>Total Unidades Vendidas</p>
        </div>
        <div class="resumen-item">
            <h3>${{ number_format($totalGanancia, 2) }}</h3>
            <p>Total Ganancia</p>
        </div>
    </div>

    @if($ventasPorProducto->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Unidades Vendidas</th>
                    <th>Comisión Unitaria</th>
                    <th>Total Ganancia</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventasPorProducto as $venta)
                    <tr>
                        <td>{{ $venta['producto_nombre'] }}</td>
                        <td>{{ number_format($venta['cantidad']) }}</td>
                        <td>${{ number_format($venta['comision'], 2) }}</td>
                        <td>${{ number_format($venta['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td>{{ number_format($totalUnidades) }}</td>
                    <td>-</td>
                    <td>${{ number_format($totalGanancia, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <p style="text-align: center; color: #666;">No hay datos de ventas para el período seleccionado.</p>
    @endif

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema de gestión de transferencias.</p>
    </div>
</body>
</html>
