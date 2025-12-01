@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Mis pedidos pendientes</span>
                    <form class="d-flex" method="GET" action="{{ route('visitador.pedidos.reporte') }}">
                        <input type="date" name="fecha_inicio" class="form-control form-control-sm me-2" value="{{ request('fecha_inicio') }}">
                        <input type="date" name="fecha_fin" class="form-control form-control-sm me-2" value="{{ request('fecha_fin') }}">
                        <button class="btn btn-primary btn-sm" type="submit">Filtrar</button>
                    </form>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($pedidos->isEmpty())
                        <p class="mb-0">No tienes pedidos pendientes.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha transferencia</th>
                                        <th>Cliente</th>
                                        <th>Droguería</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Descuento</th>
                                        <th>N° transferencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pedidos as $pedido)
                                        <tr>
                                            <td>{{ optional($pedido->transferencia->fecha_transferencia)->format('d/m/Y') }}</td>
                                            <td>{{ optional($pedido->transferencia->cliente)->nombre_cliente ?? '-' }}</td>
                                            <td>{{ optional(optional($pedido->transferencia->cliente)->drogueriaRelacion)->nombre ?? '-' }}</td>
                                            <td>{{ $pedido->producto->nombre }}</td>
                                            <td>{{ $pedido->cantidad }}</td>
                                            <td>{{ $pedido->descuento }}%</td>
                                            <td>{{ $pedido->transferencia->transferencia_numero }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
