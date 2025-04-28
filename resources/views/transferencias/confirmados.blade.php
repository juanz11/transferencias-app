@extends('layouts.app')

@section('styles')
<style>
    .table th {
        background-color: #f8f9fa;
        white-space: nowrap;
    }
    .descuento {
        color: #28a745;
        font-weight: bold;
    }
    .pedido-detalle {
        background-color: #f8f9fa;
        padding: 10px;
        margin: 5px 0;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Reporte de Transferencias por Fecha</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('transferencias.confirmados') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha">Fecha:</label>
                            <input type="date" 
                                   name="fecha" 
                                   id="fecha" 
                                   class="form-control" 
                                   value="{{ request('fecha') }}"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="visitador_id">Visitador:</label>
                            <select name="visitador_id" id="visitador_id" class="form-control">
                                <option value="">Todos los visitadores</option>
                                @foreach($visitadores as $visitador)
                                    <option value="{{ $visitador->id }}" {{ request('visitador_id') == $visitador->id ? 'selected' : '' }}>
                                        {{ $visitador->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            @if($transferencias->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Fecha Transferencia</th>
                                <th>Fecha Confirmación</th>
                                <th>Visitador</th>
                                <th>N° Transferencia</th>
                                <th>Pedidos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transferencias as $transferencia)
                                <tr>
                                    <td>{{ $transferencia['fecha_transferencia']->format('d/m/Y') }}</td>
                                    <td>{{ $transferencia['fecha_confirmacion']->format('d/m/Y H:i') }}</td>
                                    <td>{{ $transferencia['visitador'] }}</td>
                                    <td>{{ $transferencia['transferencia_numero'] }}</td>
                                    <td>
                                        @foreach($transferencia['pedidos'] as $pedido)
                                            <div class="pedido-detalle">
                                                <strong>{{ $pedido['producto'] }}</strong><br>
                                                Cantidad: {{ $pedido['cantidad'] }}<br>
                                                @if($pedido['descuento'] > 0)
                                                    <span class="descuento">Descuento: {{ $pedido['descuento'] }}%</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        <a href="{{ route('transferencias.confirmados.edit', $transferencia['id']) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    No hay transferencias confirmadas{{ request('fecha') ? ' para la fecha seleccionada' : '' }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
