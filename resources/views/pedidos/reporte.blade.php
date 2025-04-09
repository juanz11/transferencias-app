@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Reporte de Pedidos - {{ $tipoVista === 'agrupado' ? 'Vista Agrupada' : 'Vista Individual' }}</span>
                        <div>
                            <a href="{{ route('pedidos.reporte', array_merge(request()->all(), ['formato' => 'pdf'])) }}" class="btn btn-secondary me-2">Descargar PDF</a>
                            <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Volver</a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Tabla principal (agrupada o individual) -->
                    <div class="table-responsive mb-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha Transferencia</th>
                                    <th>Fecha Confirmación</th>
                                    <th>Visitador</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Descuento</th>
                                    <th>N° Transferencia</th>
                                    <th>Ganancia</th>
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
                                            <td>
                                                @php
                                                    $producto = \App\Models\Producto::where('nombre', $pedido['producto'])->first();
                                                    $ganancia = $producto ? $pedido['cantidad'] * $producto->comision : 0;
                                                @endphp
                                                ${{ number_format($ganancia, 2) }}
                                            </td>
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
                                            <td>
                                                @php
                                                    $ganancia = $pedido->cantidad * $pedido->producto->comision;
                                                @endphp
                                                ${{ number_format($ganancia, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumen por Visitador -->
                    <h4 class="mb-3">Resumen por Visitador</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
