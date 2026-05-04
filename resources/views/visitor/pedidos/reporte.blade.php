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

                        {{-- Tabla agrupada por transferencia --}}
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>N° Transferencia</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-center">Descuento</th>
                                        <th class="text-end">Comisión</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pedidosAgrupados as $grupo)
                                        @php $count = count($grupo['pedidos']); @endphp
                                        @foreach($grupo['pedidos'] as $i => $pedido)
                                            <tr @if($i === 0) class="table-light" @endif>
                                                @if($i === 0)
                                                    <td rowspan="{{ $count }}" class="fw-bold text-center align-middle">
                                                        {{ $grupo['transferencia_numero'] }}
                                                    </td>
                                                    <td rowspan="{{ $count }}" class="text-center align-middle">
                                                        {{ optional($grupo['fecha_transferencia'])->format('d/m/Y') }}
                                                    </td>
                                                    <td rowspan="{{ $count }}" class="align-middle">
                                                        {{ $grupo['cliente'] }}
                                                    </td>
                                                @endif
                                                <td>{{ $pedido->producto->nombre }}</td>
                                                <td class="text-center">{{ $pedido->cantidad }}</td>
                                                <td class="text-center">{{ $pedido->descuento }}%</td>
                                                <td class="text-end">${{ number_format($pedido->cantidad * $pedido->producto->comision, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    {{-- Total general --}}
                                    <tr class="table-dark text-white fw-bold">
                                        <td colspan="4" class="text-end">TOTAL GENERAL:</td>
                                        <td class="text-center">{{ number_format($totalProductos, 0) }}</td>
                                        <td></td>
                                        <td class="text-end">${{ number_format($totalGanancia, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Resumen por Productos --}}
                        <div class="card mt-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-box me-2"></i>Resumen por Productos</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Producto</th>
                                                <th class="text-center">Cantidad Total</th>
                                                <th class="text-end">Comisión Unitaria</th>
                                                <th class="text-end">Ganancia Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($resumenProductos as $resumen)
                                                <tr>
                                                    <td>{{ $resumen['nombre'] }}</td>
                                                    <td class="text-center"><strong>{{ number_format($resumen['cantidad'], 0) }}</strong></td>
                                                    <td class="text-end">${{ number_format($resumen['comision_unitaria'], 2) }}</td>
                                                    <td class="text-end"><strong>${{ number_format($resumen['ganancia'], 2) }}</strong></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-success">
                                            <tr class="fw-bold">
                                                <td>TOTAL</td>
                                                <td class="text-center">{{ number_format($totalProductos, 0) }}</td>
                                                <td></td>
                                                <td class="text-end">${{ number_format($totalGanancia, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
