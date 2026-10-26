@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Cobertura de Ventas Nacional</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <!-- Filtros de fechas y tipo -->
                    <form method="GET" action="{{ route('cobertura.index') }}" class="mb-4">
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
                                    <label for="tipo">Tipo de Cobertura</label>
                                    <select class="form-control" id="tipo" name="tipo">
                                        <option value="especialidad" {{ $tipo === 'especialidad' ? 'selected' : '' }}>Especialidad (Médicos)</option>
                                        <option value="farmacia" {{ $tipo === 'farmacia' ? 'selected' : '' }}>Farmacia</option>
                                        <option value="hospital" {{ $tipo === 'hospital' ? 'selected' : '' }}>Hospital</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="visitador_id">Visitador</label>
                                    <select class="form-control" id="visitador_id" name="visitador_id">
                                        <option value="0" {{ $visitadorId == 0 || !$visitadorId ? 'selected' : '' }}>Todos</option>
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
                                    <button type="submit" class="btn btn-primary">Calcular Cobertura</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Resultados de cobertura -->
                    @if($resultados)
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Objetivo</h6>
                                        <h3 class="card-text">{{ number_format($resultados['objetivo_total']) }}</h3>
                                        <small class="text-muted">{{ $resultados['objetivos_diarios'] }} / día × {{ $resultados['dias_habiles'] }} días</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Visitados</h6>
                                        <h3 class="card-text">{{ number_format($resultados['visitados']) }}</h3>
                                        <small class="text-muted">Total {{ ucfirst($resultados['tipo']) }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Base</h6>
                                        <h3 class="card-text">{{ number_format($resultados['base']) }}</h3>
                                        <small class="text-muted">{{ ucfirst($resultados['tipo']) }} en base</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card @if($resultados['cobertura'] >= 100) bg-success @elseif($resultados['cobertura'] >= 70) bg-warning @else bg-danger @endif">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Cobertura</h6>
                                        <h3 class="card-text">{{ number_format($resultados['cobertura'], 1) }}%</h3>
                                        <small class="text-white">Cumplimiento</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de detalle -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Detalle de Visitas ({{ ucfirst($resultados['tipo']) }})</h6>
                                @if($visitadorId && $visitadorId > 0)
                                    <small class="text-muted">Visitador: {{ $visitadorNombre }}</small>
                                @endif
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nombre</th>
                                                <th>Fecha</th>
                                                <th>Visitador</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(is_array($resultados['detalle']) && count($resultados['detalle']) > 0)
                                                @foreach($resultados['detalle'] as $index => $item)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $item['nombre'] ?? $item['medico'] ?? $item['farmacia'] ?? $item['hospital'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['fecha'] ?? 'N/A' }}</td>
                                                        <td>{{ $item['visitador'] ?? 'N/A' }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="4" class="text-center">No hay datos de visitas en el período seleccionado.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            Selecciona un período de fechas y un tipo de cobertura para calcular los resultados.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
