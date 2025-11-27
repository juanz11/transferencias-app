@extends('layouts.app')

@section('content')
<style>
    .welcome-header {
        text-align: center;
        margin-bottom: 3rem;
        animation: fadeIn 0.6s ease-in;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .welcome-header img {
        max-height: 150px;
        margin-bottom: 1.5rem;
        filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.3));
    }
    
    .welcome-header h1 {
        color: #ffffff;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }
    
    .welcome-header p {
        color: #e0e0e0;
        font-size: 1.2rem;
    }
    
    .modern-card {
        background-color: rgba(30, 35, 45, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        border: none;
        padding: 2rem;
        height: 100%;
        transition: all 0.3s ease;
        animation: slideUp 0.6s ease-out;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
    }
    
    .modern-card .card-title {
        color: #ffffff;
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
    }
    
    .modern-card .card-title i {
        font-size: 2rem;
        margin-right: 1rem;
        color: rgb(31, 69, 145);
    }
    
    .btn-modern {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn-modern-primary {
        background-color: rgb(31, 69, 145);
        color: white;
    }
    
    .btn-modern-primary:hover {
        background-color: rgb(25, 55, 120);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(31, 69, 145, 0.4);
        color: white;
    }
    
    .btn-modern-secondary {
        background-color: rgba(52, 152, 219, 0.2);
        color: #3498db;
        border: 2px solid #3498db;
    }
    
    .btn-modern-secondary:hover {
        background-color: #3498db;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
    }
    
    .btn-modern-success {
        background-color: #27ae60;
        color: white;
    }
    
    .btn-modern-success:hover {
        background-color: #229954;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(39, 174, 96, 0.4);
        color: white;
    }
    
    .btn-modern i {
        margin-right: 0.5rem;
    }
</style>

<div class="container">
    <div class="welcome-header">
        <img src="{{ asset('logo/logo.png') }}" alt="Logo" class="img-fluid">
        <h1>Control de Transferencias</h1>
        <p>Gestione sus transferencias y pedidos de manera eficiente</p>
    </div>

    @auth
        @if(auth()->user()->rol === 'admin')
            <div class="row">
                <!-- Card de Transferencias -->
                <div class="col-md-6 mb-4">
                    <div class="modern-card">
                        <h2 class="card-title">
                            <i class="fas fa-exchange-alt"></i>Transferencias
                        </h2>
                        <div class="d-grid gap-3">
                            <a href="{{ route('transferencias.pedidos.create') }}" 
                               class="btn btn-modern btn-modern-primary">
                                <i class="fas fa-plus"></i>Nueva Transferencia
                            </a>
                            
                            <a href="{{ route('transferencias.reporte') }}" 
                               class="btn btn-modern btn-modern-secondary">
                                <i class="fas fa-chart-bar"></i>Reporte
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card de Pedidos -->
                <div class="col-md-6 mb-4">
                    <div class="modern-card">
                        <h2 class="card-title">
                            <i class="fas fa-clipboard-list"></i>Pedidos
                        </h2>
                        <div class="d-grid gap-3">
                            <a href="{{ route('pedidos.index') }}" 
                               class="btn btn-modern btn-modern-success">
                                <i class="fas fa-list-alt"></i>Ver Pedidos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endauth
</div>
@endsection
