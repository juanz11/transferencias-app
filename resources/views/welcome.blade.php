@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-8 text-center">
            <img src="{{ asset('logo/login-bg.png') }}" alt="Logo" class="img-fluid mb-4" style="max-height: 150px;">
            <p class="lead text-muted">Gestione sus transferencias y pedidos de manera eficiente</p>
        </div>
    </div>

    <div class="row">
        <!-- Card de Transferencias -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title h4 mb-4">
                        <i class="fas fa-exchange-alt me-2"></i>Transferencias
                    </h2>
                    <div class="d-grid gap-3">
                        <a href="{{ route('transferencias.pedidos.create') }}" 
                           class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Nueva Transferencia
                        </a>
                        
                        <a href="{{ route('transferencias.reporte') }}" 
                           class="btn btn-outline-info btn-lg">
                            <i class="fas fa-chart-bar me-2"></i>Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de Pedidos -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title h4 mb-4">
                        <i class="fas fa-clipboard-list me-2"></i>Pedidos
                    </h2>
                    <div class="d-grid gap-3">
                        <a href="{{ route('pedidos.index') }}" 
                           class="btn btn-success btn-lg">
                            <i class="fas fa-list-alt me-2"></i>Ver Pedidos
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
@endpush
@endsection
