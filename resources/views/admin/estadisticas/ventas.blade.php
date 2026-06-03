@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Estadísticas de Ventas</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- Filtro de fechas y visitador -->
                    <form method="GET" action="{{ route('admin.estadisticas.ventas') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="visitador_id">Visitador</label>
                                    <select class="form-control" id="visitador_id" name="visitador_id">
                                        <option value="todos" {{ $visitadorId === 'todos' || !$visitadorId ? 'selected' : '' }}>Todos</option>
                                        @foreach($visitadores as $visitador)
                                            <option value="{{ $visitador->id }}" {{ $visitadorId == $visitador->id ? 'selected' : '' }}>
                                                {{ $visitador->nombre }}
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
                                        <a href="{{ route('admin.estadisticas.ventas') }}" class="btn btn-secondary">Limpiar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Resumen -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Unidades Vendidas</h6>
                                    <h3 class="card-text">{{ number_format($totalUnidades) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Ganancia</h6>
                                    <h3 class="card-text">${{ number_format($totalGanancia, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico -->
                    @if(!empty($chartLabels) && !empty($chartData))
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Gráfico de Ventas por Producto</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="ventasChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    @endif

                    <!-- Tabla de ventas por producto -->
                    @if($ventasPorProducto->isNotEmpty())
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Ventas por Producto</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
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
                                            <tr class="font-weight-bold">
                                                <td>Total</td>
                                                <td>{{ number_format($totalUnidades) }}</td>
                                                <td>-</td>
                                                <td>${{ number_format($totalGanancia, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No hay datos de ventas para el período seleccionado.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if(!empty($chartLabels) && !empty($chartData))
        const ctx = document.getElementById('ventasChart').getContext('2d');
        const ventasChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Unidades Vendidas',
                    data: @json($chartData),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Unidades'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Productos'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    @endif
</script>
@endsection
