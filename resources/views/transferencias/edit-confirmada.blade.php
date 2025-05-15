@extends('layouts.app')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .pedido-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .producto-nombre {
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 10px;
    }
    .producto-row {
        transition: all 0.3s ease;
    }
    .producto-row:hover {
        background-color: #f0f2f5;
    }
    .select2-container {
        width: 100% !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Transferencia Confirmada</h3>
        </div>
        <div class="card-body">
            <form id="editForm" action="{{ route('transferencias.confirmados.update', $transferenciaConfirmada->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_transferencia">Fecha Transferencia:</label>
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

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="fecha_confirmacion">Fecha Confirmación:</label>
                            <input type="date" 
                                   name="fecha_confirmacion" 
                                   id="fecha_confirmacion"
                                   class="form-control @error('fecha_confirmacion') is-invalid @enderror"
                                   value="{{ old('fecha_confirmacion', $transferenciaConfirmada->created_at->format('Y-m-d')) }}"
                                   required>
                            @error('fecha_confirmacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="transferencia_numero">N° Transferencia:</label>
                            <input type="text" 
                                   name="transferencia_numero" 
                                   id="transferencia_numero"
                                   class="form-control @error('transferencia_numero') is-invalid @enderror"
                                   value="{{ old('transferencia_numero', $transferenciaConfirmada->transferencia->transferencia_numero) }}"
                                   required>
                            @error('transferencia_numero')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="visitador_id">Visitador:</label>
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

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Productos</h4>
                        <button type="button" class="btn btn-secondary" id="agregar-producto">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="productos-container">
                            @foreach($transferenciaConfirmada->pedidosConfirmados as $pedido)
                                <div class="producto-row border p-3 mb-3 rounded">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Seleccione un producto</label>
                                            <select class="form-select producto-select" name="productos[]" required>
                                                <option value="">Seleccione un producto</option>
                                                @foreach($productos as $producto)
                                                    <option value="{{ $producto->id }}" {{ $pedido->producto_id == $producto->id ? 'selected' : '' }}>
                                                        {{ $producto->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Cantidad</label>
                                            <input type="number" class="form-control" name="cantidades[]" min="1" value="{{ $pedido->cantidad }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Descuento</label>
                                            <input type="number" class="form-control" name="descuentos[]" min="0" max="100" value="{{ $pedido->descuento }}">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-danger eliminar-producto">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('transferencias.confirmados') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template para nuevas filas de producto -->
<template id="producto-template">
    <div class="producto-row border p-3 mb-3 rounded">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Seleccione un producto</label>
                <select class="form-select producto-select" name="productos[]" required>
                    <option value="">Seleccione un producto</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cantidad</label>
                <input type="number" class="form-control" name="cantidades[]" min="1" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Descuento</label>
                <input type="number" class="form-control" name="descuentos[]" min="0" max="100">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger eliminar-producto">
                    <i class="fas fa-trash"></i>
                </button>
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
                $(this).closest('.producto-row').remove();
            } else {
                alert('Debe mantener al menos un producto');
            }
        });
    });
</script>
@endpush
