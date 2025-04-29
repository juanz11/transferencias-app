@extends('layouts.app')

@section('styles')
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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Transferencia Confirmada</h3>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <strong>Fecha Transferencia:</strong>
                    <p>{{ $transferenciaConfirmada->transferencia->fecha_transferencia->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-4">
                    <strong>N° Transferencia:</strong>
                    <p>{{ $transferenciaConfirmada->transferencia->transferencia_numero }}</p>
                </div>
                <div class="col-md-4">
                    <strong>Visitador:</strong>
                    <p>{{ $transferenciaConfirmada->transferencia->visitador->nombre ?? 'Sin Visitador' }}</p>
                </div>
            </div>

            <form id="editForm" action="{{ route('transferencias.confirmados.update', $transferenciaConfirmada->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_transferencia">Fecha Transferencia:</label>
                            <input type="date" 
                                   name="fecha_transferencia" 
                                   class="form-control @error('fecha_transferencia') is-invalid @enderror"
                                   value="{{ old('fecha_transferencia', $transferenciaConfirmada->fecha_transferencia ? $transferenciaConfirmada->fecha_transferencia->format('Y-m-d') : $transferenciaConfirmada->transferencia->fecha_transferencia->format('Y-m-d')) }}"
                                   required>
                            @error('fecha_transferencia')
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
                    <a href="{{ route('transferencias.confirmados') }}" class="btn btn-secondary me-2">Cancelar</a>
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
                <input type="number" class="form-control" name="descuentos[]" min="0" max="100" value="0">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger eliminar-producto">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('productos-container');
    const template = document.getElementById('producto-template');
    const btnAgregar = document.getElementById('agregar-producto');

    // Función para agregar una nueva fila de producto
    function agregarProducto() {
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);

        // Agregar evento para eliminar producto
        const btnEliminar = container.lastElementChild.querySelector('.eliminar-producto');
        btnEliminar.addEventListener('click', function() {
            this.closest('.producto-row').remove();
        });
    }

    // Agregar eventos para eliminar productos existentes
    document.querySelectorAll('.eliminar-producto').forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.producto-row').remove();
        });
    });

    // Evento para agregar más productos
    btnAgregar.addEventListener('click', agregarProducto);

    // Validar el formulario antes de enviar
    document.getElementById('editForm').addEventListener('submit', function(e) {
        const productos = container.querySelectorAll('.producto-row');
        if (productos.length === 0) {
            e.preventDefault();
            alert('Debe tener al menos un producto');
        }
    });
});
</script>
@endpush
