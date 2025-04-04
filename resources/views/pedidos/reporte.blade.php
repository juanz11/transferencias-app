@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Reporte de Pedidos</span>
                        <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Volver</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Visitador</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Descuento</th>
                                    <th>N° Transferencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedidos as $pedido)
                                    <tr>
                                        <td>{{ $pedido->transferenciaConfirmada->transferencia->fecha_transferencia->format('d/m/Y') }}</td>
                                        <td>{{ $pedido->transferenciaConfirmada->transferencia->visitador->nombre }}</td>
                                        <td>{{ $pedido->producto->nombre }}</td>
                                        <td>{{ $pedido->cantidad }}</td>
                                        <td>{{ $pedido->descuento }}%</td>
                                        <td>{{ $pedido->transferenciaConfirmada->transferencia->transferencia_numero }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total Productos Vendidos:</strong></td>
                                    <td><strong>{{ $totalProductos }}</strong></td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
