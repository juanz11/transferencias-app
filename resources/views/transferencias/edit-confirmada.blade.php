@extends('layouts.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container {
        width: 100% !important;
    }
    
    /* Custom Divider Style */
    .divider-custom {
        display: flex;
        align-items: center;
        text-align: center;
        font-weight: 600;
        font-size: 1.1rem;
        color: #ffffff;
        margin: 2rem 0 1rem 0;
    }
    
    .divider-custom::before,
    .divider-custom::after {
        content: '';
        flex: 1;
        border-bottom: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    .divider-custom::before {
        margin-right: 1rem;
    }
    
    .divider-custom::after {
        margin-left: 1rem;
    }
    
    /* Product Cards */
    .producto-item .card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: 2px solid #e9ecef;
    }
    
    .producto-item .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        border-color: #0d6efd;
    }
    
    /* Input Groups */
    .input-group-text {
        font-weight: 600;
    }
    
    /* Form Labels */
    .form-label.fw-bold {
        color: #e0e0e0;
        margin-bottom: 0.75rem;
    }
    
    /* Custom Background */
    .card-custom-bg {
        background-color: rgb(68, 78, 98) !important;
        color: #ffffff;
    }
    
    .card-custom-bg .form-control,
    .card-custom-bg .form-select {
        background-color: rgba(255, 255, 255, 0.95);
        border-color: rgba(255, 255, 255, 0.3);
        color: #212529;
    }
    
    .card-custom-bg .form-control:focus,
    .card-custom-bg .form-select:focus {
        background-color: #ffffff;
        border-color: #86b7fe;
    }
    
    .card-custom-bg .divider-custom {
        color: #ffffff;
    }
    
    .card-custom-bg .divider-custom::before,
    .card-custom-bg .divider-custom::after {
        border-bottom-color: rgba(255, 255, 255, 0.3);
    }
    
    .card-custom-bg .producto-item .card {
        background-color: rgba(255, 255, 255, 0.98);
        border-color: rgba(255, 255, 255, 0.5);
    }
    
    .card-custom-bg .producto-item .card:hover {
        background-color: #ffffff;
        border-color: #0d6efd;
    }
    
    .card-custom-bg .form-label {
        color: #e0e0e0;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card shadow-lg card-custom-bg">
                <div class="card-body p-4">
                    <form id="editForm" action="{{ route('transferencias.confirmados.update', $transferenciaConfirmada->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Divider Fechas y Datos -->
                        <div class="divider-custom mb-4">
                            <i class="fas fa-calendar me-2"></i>Fechas y Número de Transferencia
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <label for="fecha_transferencia" class="form-label text-center d-block fw-bold">Fecha Transferencia</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white">
                                        <i class="fas fa-arrow-right-arrow-left"></i>
                                    </span>
                                    <input type="date" 
                                           name="fecha_transferencia" 
                                           id="fecha_transferencia"
                                           class="form-control @error('fecha_transferencia') is-invalid @enderror"
                                           value="{{ old('fecha_transferencia', $transferenciaConfirmada->fecha_transferencia ? $transferenciaConfirmada->fecha_transferencia->format('Y-m-d') : $transferenciaConfirmada->transferencia->fecha_transferencia->format('Y-m-d')) }}"
                                           required>
                                    @error('fecha_transferencia')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="fecha_confirmacion" class="form-label text-center d-block fw-bold">Fecha Confirmación</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-info text-white">
                                        <i class="fas fa-calendar-check"></i>
                                    </span>
                                    <input type="datetime-local" 
                                           name="fecha_confirmacion" 
                                           id="fecha_confirmacion"
                                           class="form-control @error('fecha_confirmacion') is-invalid @enderror"
                                           value="{{ old('fecha_confirmacion', $transferenciaConfirmada->created_at->format('Y-m-d\\TH:i')) }}"
                                           required>
                                    @error('fecha_confirmacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="transferencia_numero" class="form-label text-center d-block fw-bold">N° Transferencia</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-danger text-white">
                                        <i class="fas fa-hashtag"></i>
                                    </span>
                                    <input type="text" 
                                           name="transferencia_numero" 
                                           id="transferencia_numero"
                                           class="form-control fw-bold @error('transferencia_numero') is-invalid @enderror"
                                           value="{{ old('transferencia_numero', $transferenciaConfirmada->transferencia->transferencia_numero) }}"
                                           required>
                                    @error('transferencia_numero')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="visitador_id" class="form-label text-center d-block fw-bold">Visitador</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white">
                                        <i class="fas fa-briefcase"></i>
                                    </span>
                                    <select name="visitador_id" 
                                            id="visitador_id" 
                                            class="form-select @error('visitador_id') is-invalid @enderror" 
                                            required>
                                        <option value="">Seleccione un visitador</option>
                                        @foreach($visitadores as $visitador)
                                            <option value="{{ $visitador->id }}" 
                                                {{ old('visitador_id', $transferenciaConfirmada->transferencia->visitador_id) == $visitador->id ? 'selected' : '' }}>
                                                {{ $visitador->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('visitador_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Divider Productos -->
                        <div class="divider-custom mb-4">
                            <i class="fas fa-box me-2"></i>Productos
                        </div>
                        
                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-primary btn-lg" id="agregar-producto">
                                <i class="fas fa-plus me-2"></i>Agregar Producto
                            </button>
                        </div>

                        <div id="productos-container" class="row g-3">
                            @foreach($transferenciaConfirmada->pedidosConfirmados as $pedido)
                                <div class="col-12 col-md-6 col-lg-4 producto-item">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0"><i class="fas fa-box text-primary me-2"></i>Producto</h6>
                                                <button type="button" class="btn btn-danger btn-sm eliminar-producto">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <select class="form-select producto-select mb-3" name="productos[]" required>
                                                <option value="">Seleccione un producto</option>
                                                @foreach($productos as $producto)
                                                    <option value="{{ $producto->id }}" {{ $pedido->producto_id == $producto->id ? 'selected' : '' }}>
                                                        {{ $producto->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="input-group mb-2">
                                                <span class="input-group-text">Des</span>
                                                <input type="number" class="form-control" name="descuentos[]" placeholder="%" min="0" max="100" value="{{ $pedido->descuento }}">
                                            </div>
                                            <div class="input-group">
                                                <span class="input-group-text">Unds</span>
                                                <input type="number" class="form-control" name="cantidades[]" placeholder="Cantidad" min="1" value="{{ $pedido->cantidad }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('transferencias.confirmados') }}" class="btn btn-secondary btn-lg px-4 me-2">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template para nuevas filas de producto -->
<template id="producto-template">
    <div class="col-12 col-md-6 col-lg-4 producto-item">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="fas fa-box text-primary me-2"></i>Producto</h6>
                    <button type="button" class="btn btn-danger btn-sm eliminar-producto">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <select class="form-select producto-select mb-3" name="productos[]" required>
                    <option value="">Seleccione un producto</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                    @endforeach
                </select>
                <div class="input-group mb-2">
                    <span class="input-group-text">Des</span>
                    <input type="number" class="form-control" name="descuentos[]" placeholder="%" min="0" max="100">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Unds</span>
                    <input type="number" class="form-control" name="cantidades[]" placeholder="Cantidad" min="1" required>
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Inicializar Select2 para el visitador
        $('#visitador_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Seleccione un visitador',
            allowClear: true
        });

        // Inicializar Select2 para los productos
        function initializeSelect2(element) {
            $(element).select2({
                theme: 'bootstrap-5',
                placeholder: 'Seleccione un producto',
                allowClear: true
            });
        }

        // Inicializar Select2 en los productos existentes
        $('.producto-select').each(function() {
            initializeSelect2(this);
        });

        // Agregar nuevo producto
        $('#agregar-producto').click(function() {
            const template = document.querySelector('#producto-template');
            const clone = document.importNode(template.content, true);
            document.querySelector('#productos-container').appendChild(clone);
            
            // Inicializar Select2 en el nuevo select
            const newSelect = document.querySelector('#productos-container').lastElementChild.querySelector('.producto-select');
            initializeSelect2($(newSelect));
        });

        // Eliminar producto
        $(document).on('click', '.eliminar-producto', function() {
            const container = document.querySelector('#productos-container');
            if (container.children.length > 1) {
                $(this).closest('.producto-item').remove();
            } else {
                alert('Debe mantener al menos un producto');
            }
        });
    });
</script>
@endpush
