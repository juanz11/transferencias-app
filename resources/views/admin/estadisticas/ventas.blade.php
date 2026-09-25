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

                    <!-- Filtro de fechas, visitador, droguería y zona -->
                    <form method="GET" action="{{ route('admin.estadisticas.ventas') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha Inicio</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="{{ $fechaInicio ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha Fin</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="{{ $fechaFin ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-2">
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
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="drogueria_id">Droguería</label>
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
                            <div class="col-md-2">
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
                            <div class="col-md-2">
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

                    <!-- Botón descargar PDF -->
                    <div class="mb-4">
                        <form method="POST" action="{{ route('admin.estadisticas.ventas.pdf') }}">
                            @csrf
                            <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio ?? '' }}">
                            <input type="hidden" name="fecha_fin" value="{{ $fechaFin ?? '' }}">
                            <input type="hidden" name="visitador_id" value="{{ $visitadorId ?? 'todos' }}">
                            <input type="hidden" name="drogueria_id" value="{{ $drogueriaId ?? 'todas' }}">
                            <input type="hidden" name="zona" value="{{ $zona ?? 'todas' }}">
                            <input type="hidden" name="chart_image" id="chart_image">
                            <button type="submit" class="btn btn-success" onclick="return prepararPdf()">
                                <i class="fas fa-file-pdf me-2"></i>Descargar PDF
                            </button>
                        </form>
                    </div>

                    <!-- Resumen -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Transferencias</h6>
                                    <h3 class="card-text">{{ number_format($totalTransferencias) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Unidades Vendidas</h6>
                                    <h3 class="card-text">{{ number_format($totalUnidades) }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
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
        @php
            $filtros = [];
            if ($fechaInicio && $fechaFin) {
                $filtros[] = 'Fecha: ' . \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') . ' a ' . \Carbon\Carbon::parse($fechaFin)->format('d/m/Y');
            }
            if ($visitadorId && $visitadorId !== 'todos') {
                $filtros[] = 'Visitador: ' . ($visitadores->firstWhere('id', $visitadorId)?->nombre ?? $visitadorId);
            }
            if ($drogueriaId && $drogueriaId !== 'todas') {
                $filtros[] = 'Droguería: ' . ($droguerias->firstWhere('id', $drogueriaId)?->nombre ?? $drogueriaId);
            }
            if ($zona && $zona !== 'todas') {
                $filtros[] = 'Zona: ' . $zona;
            }
            $filtroTexto = $filtros ? 'Filtrado por: ' . implode('  •  ', $filtros) : '';
        @endphp
        const chartLabels = @json($chartLabels);
        const filtroTexto = @json($filtroTexto);
        const palette = [
            ['rgba(54, 130, 235, 0.85)', 'rgb(40, 100, 200)'],
            ['rgba(235, 80, 80, 0.85)', 'rgb(200, 55, 55)'],
            ['rgba(245, 195, 60, 0.85)', 'rgb(200, 155, 35)'],
            ['rgba(60, 180, 100, 0.85)', 'rgb(40, 145, 75)'],
            ['rgba(150, 95, 220, 0.85)', 'rgb(115, 70, 180)']
        ];
        function buildChartConfig() {
            return {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Unidades Vendidas',
                        data: @json($chartData),
                        backgroundColor: chartLabels.map((_, i) => palette[i % palette.length][0]),
                        borderColor: chartLabels.map((_, i) => palette[i % palette.length][1]),
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 80
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
                            },
                            ticks: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: filtroTexto.length > 0,
                            text: filtroTexto,
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            color: '#555',
                            padding: {
                                bottom: 15
                            }
                        }
                    }
                }
            };
        }

        const ctx = document.getElementById('ventasChart').getContext('2d');
        const ventasChart = new Chart(ctx, buildChartConfig());
    @endif

    function prepararPdf() {
        if (typeof ventasChart === 'undefined') {
            return true;
        }
        const n = chartLabels.length;
        const maxH = 1850;
        const perRow = Math.max(26, Math.min(120, Math.floor((maxH - 240) / Math.max(1, n))));
        const exportCanvas = document.createElement('canvas');
        exportCanvas.width = 1600;
        exportCanvas.height = Math.min(maxH, Math.max(900, n * perRow + 240));
        const rowH = (exportCanvas.height - 240) / Math.max(1, n);
        const tickFont = Math.max(13, Math.min(24, Math.floor(rowH * 0.42)));
        const productFont = Math.max(16, Math.min(30, Math.floor(rowH * 0.55)));
        const cfg = buildChartConfig();
        cfg.data.datasets[0].maxBarThickness = Math.max(14, Math.floor(rowH * 0.55));
        cfg.data.datasets[0].barPercentage = 0.7;
        cfg.data.datasets[0].categoryPercentage = 0.7;
        cfg.options.indexAxis = 'y';
        cfg.options.responsive = false;
        cfg.options.maintainAspectRatio = true;
        cfg.options.animation = false;
        cfg.options.layout = { padding: { top: 20, right: 100, bottom: 20, left: 20 } };
        cfg.options.scales = {
            x: {
                beginAtZero: true,
                grace: '10%',
                title: {
                    display: true,
                    text: 'Unidades',
                    font: { size: 26, weight: 'bold' }
                },
                ticks: { font: { size: tickFont } }
            },
            y: {
                title: {
                    display: true,
                    text: 'Productos',
                    font: { size: 26, weight: 'bold' }
                },
                ticks: {
                    autoSkip: false,
                    color: '#000',
                    font: { size: productFont, weight: 'bold' }
                }
            }
        };
        cfg.options.plugins.title.font.size = 32;
        const valFont = Math.max(11, Math.min(22, Math.floor(rowH * 0.4)));
        cfg.plugins = [{
            id: 'barValues',
            afterDatasetsDraw(chart) {
                const c = chart.ctx;
                chart.data.datasets.forEach((dataset, i) => {
                    chart.getDatasetMeta(i).data.forEach((bar, index) => {
                        c.save();
                        c.fillStyle = '#000';
                        c.font = 'bold ' + valFont + 'px Arial';
                        c.textAlign = 'left';
                        c.textBaseline = 'middle';
                        c.fillText(Number(dataset.data[index]).toLocaleString('es-ES'), bar.x + 12, bar.y);
                        c.restore();
                    });
                });
            }
        }];
        const tmpChart = new Chart(exportCanvas.getContext('2d'), cfg);
        const final = document.createElement('canvas');
        final.width = exportCanvas.width;
        final.height = exportCanvas.height;
        const fctx = final.getContext('2d');
        fctx.fillStyle = '#ffffff';
        fctx.fillRect(0, 0, final.width, final.height);
        fctx.drawImage(exportCanvas, 0, 0);
        document.getElementById('chart_image').value = final.toDataURL('image/png');
        tmpChart.destroy();
        return true;
    }
</script>
@endsection
