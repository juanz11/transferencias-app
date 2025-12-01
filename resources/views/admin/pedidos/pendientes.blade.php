@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pedidos pendientes</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if($transferencias->isEmpty())
                        <p class="mb-0">No hay pedidos pendientes.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>N° Transferencia</th>
                                    <th>Fecha transferencia</th>
                                    <th>Visitador</th>
                                    <th>Cliente</th>
                                    <th>Productos (pendientes)</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($transferencias as $transferencia)
                                    <tr>
                                        <td>{{ $transferencia->transferencia_numero }}</td>
                                        <td>{{ optional($transferencia->fecha_transferencia)->format('d/m/Y') }}</td>
                                        <td>{{ optional($transferencia->visitador)->nombre }}</td>
                                        <td>{{ optional($transferencia->cliente)->nombre_cliente }}</td>
                                        <td>
                                            <ul class="mb-0">
                                                @foreach($transferencia->pedidos->where('estado', 'pendiente') as $pedido)
                                                    <li>
                                                        {{ $pedido->producto->nombre }}:
                                                        {{ $pedido->cantidad }} unds ({{ $pedido->descuento }}% desc)
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.pedidos.cambiar-estado', $transferencia) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="estado" value="aprobado">
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Aprobar y confirmar estos pedidos?')">Aprobar</button>
                                            </form>
                                            <form action="{{ route('admin.pedidos.cambiar-estado', $transferencia) }}" method="POST" class="d-inline ms-1">
                                                @csrf
                                                <input type="hidden" name="estado" value="rechazado">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Rechazar estos pedidos?')">Rechazar</button>
                                            </form>
                                        </td>
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
