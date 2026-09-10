@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pedidos pendientes</h5>
                    <a href="{{ route('admin.pedidos.pendientes.pdf', ['drogueria_id' => $drogueriaId, 'zona' => $zona]) }}" class="btn btn-danger btn-sm">
                        <i class="fas fa-file-pdf me-1"></i> Descargar PDF
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="GET" action="{{ route('admin.pedidos.pendientes') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="drogueria_id">Drogueria</label>
                                    <select class="form-control" id="drogueria_id" name="drogueria_id">
                                        <option value="todas" {{ $drogueriaId === 'todas' || !$drogueriaId ? 'selected' : '' }}>Todas</option>
                                        @foreach($droguerias as $drogueria)
                                            <option value="{{ $drogueria->id }}" {{ $drogueriaId == $drogueria->id ? 'selected' : '' }}>
                                                {{ $drogueria->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="zona">Zona</label>
                                    <select class="form-control" id="zona" name="zona">
                                        <option value="todas" {{ $zona === 'todas' || !$zona ? 'selected' : '' }}>Todas</option>
                                        @foreach($zonas as $z)
                                            <option value="{{ $z }}" {{ $zona == $z ? 'selected' : '' }}>
                                                {{ $z }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1">Filtrar</button>
                                        <a href="{{ route('admin.pedidos.pendientes') }}" class="btn btn-secondary">Limpiar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if($transferencias->isEmpty())
                        <p class="mb-0">No hay pedidos pendientes.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>N# Transferencia</th>
                                    <th>Fecha transferencia</th>
                                    <th>Visitador</th>
                                    <th>Cliente</th>
                                    <th>Drogueria</th>
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
                                        <td>{{ $transferencia->drogueria_nombre ?? '' }}</td>
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
                                            <a href="{{ route('admin.pedidos.show', $transferencia) }}" class="btn btn-primary btn-sm">
                                                Ver detalle
                                            </a>

                                            <form action="{{ route('admin.pedidos.destroy', $transferencia) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Seguro que deseas eliminar los pedidos pendientes de esta transferencia?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Eliminar
                                                </button>
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
