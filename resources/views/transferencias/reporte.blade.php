@extends('layouts.app')
@php
use Carbon\Carbon;
@endphp

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
                                    <th>Fecha Confirmación</th>
                                    <th>Factura</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transferencias as $transferencia)
                                    <tr>
                                        <td>{{ date('d/m/Y', strtotime($transferencia->fecha_transferencia)) }}</td>
                                        <td>{{ $transferencia->visitador->nombre }}</td>
                                        <td>{{ $transferencia->transferencia_numero }}</td>
                                        <td>
                                            <span class="badge bg-success">Confirmada</span>
                                        </td>
                                        <td>
                                            {{ date('d/m/Y H:i', strtotime($transferencia->fecha_confirmacion)) }}
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
