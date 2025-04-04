@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Reporte de Transferencias</span>
                        <a href="{{ route('transferencias.index') }}" class="btn btn-secondary">Volver</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Visitador</th>
                                    <th>Número de Transferencia</th>
                                    <th>Estado</th>
                                    <th>Factura</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transferencias as $transferencia)
                                    <tr>
                                        <td>{{ $transferencia->fecha_transferencia->format('d/m/Y') }}</td>
                                        <td>{{ $transferencia->visitador->nombre }}</td>
                                        <td>{{ $transferencia->transferencia_numero }}</td>
                                        <td>
                                            @if($transferencia->confirmada)
                                                <span class="badge bg-success">Confirmada</span>
                                            @else
                                                <span class="badge bg-warning">No Confirmada</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transferencia->factura_path)
                                                <a href="{{ asset($transferencia->factura_path) }}" target="_blank" class="btn btn-sm btn-info">
                                                    Ver Factura
                                                </a>
                                            @else
                                                <span class="text-muted">Sin factura</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
