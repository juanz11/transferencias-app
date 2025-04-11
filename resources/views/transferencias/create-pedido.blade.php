@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Crear Pedido y Transferencia Confirmada</h3>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('transferencias.pedidos.store') }}" method="POST" id="pedidoForm">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="visitador_id" class="form-label">Visitador</label>
                            <select name="visitador_id" id="visitador_id" class="form-select @error('visitador_id') is-invalid @enderror" required>
                                <option value="">Seleccione un visitador</option>
                                @foreach($visitadores as $visitador)
                                    <option value="{{ $visitador->id }}" {{ old('visitador_id') == $visitador->id ? 'selected' : '' }}>
                                        {{ $visitador->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('visitador_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="cliente_id" class="form-label">Cliente</label>
                            <select name="codigo_cliente" id="cliente_id" class="form-select @error('codigo_cliente') is-invalid @enderror" required>
                                <option value="">Seleccione un cliente</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->codigo_cliente }}" {{ old('codigo_cliente') == $cliente->codigo_cliente ? 'selected' : '' }}>
                                        {{ $cliente->nombre_cliente }} - {{ $cliente->codigo_cliente }}
                                    </option>
                                @endforeach
                            </select>
                            @error('codigo_cliente')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="fecha_correo" class="form-label">Fecha de Correo</label>
                            <input type="date" name="fecha_correo" id="fecha_correo" 
                                class="form-control @error('fecha_correo') is-invalid @enderror" 
                                value="{{ old('fecha_correo') }}" required>
                            @error('fecha_correo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label for="fecha_transferencia" class="form-label">Fecha de Transferencia</label>
                            <input type="date" name="fecha_transferencia" id="fecha_transferencia" 
                                class="form-control @error('fecha_transferencia') is-invalid @enderror" 
                                value="{{ old('fecha_transferencia') }}" required>
                            @error('fecha_transferencia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4">
                            <label for="transferencia_numero" class="form-label">Número de Transferencia</label>
                            <input type="text" name="transferencia_numero" id="transferencia_numero" 
                                class="form-control @error('transferencia_numero') is-invalid @enderror" 
                                value="{{ old('transferencia_numero') }}" required
                                placeholder="Ingrese un número único">
                            @error('transferencia_numero')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Este número debe ser único y no debe existir en otras transferencias.</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Productos</h4>
                        </div>
                        <div class="card-body">
                            <div id="productos-list">
                                @if(old('productos'))
                                    @foreach(old('productos') as $key => $oldProducto)
                                        <div class="producto-item row mb-2">
                                            <div class="col-md-5">
                                                <select name="productos[{{ $key }}][id]" class="form-select producto-select @error('productos.'.$key.'.id') is-invalid @enderror" required>
                                                    <option value="">Seleccione un producto</option>
                                                    @foreach($productos as $producto)
                                                        <option value="{{ $producto->id }}" {{ $oldProducto['id'] == $producto->id ? 'selected' : '' }}>
                                                            {{ $producto->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('productos.'.$key.'.id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" name="productos[{{ $key }}][cantidad]" 
                                                    class="form-control @error('productos.'.$key.'.cantidad') is-invalid @enderror"
                                                    value="{{ $oldProducto['cantidad'] }}" 
                                                    placeholder="Cantidad" required min="1">
                                                @error('productos.'.$key.'.cantidad')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-3">
                                                <input type="number" name="productos[{{ $key }}][descuento]" 
                                                    class="form-control @error('productos.'.$key.'.descuento') is-invalid @enderror"
                                                    value="{{ $oldProducto['descuento'] }}"
                                                    placeholder="Descuento" step="0.01" min="0">
                                                @error('productos.'.$key.'.descuento')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-danger btn-sm remove-producto" {{ $key == 0 ? 'disabled' : '' }}>X</button>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="producto-item row mb-2">
                                        <div class="col-md-5">
                                            <select name="productos[0][id]" class="form-select producto-select" required>
                                                <option value="">Seleccione un producto</option>
                                                @foreach($productos as $producto)
                                                    <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="productos[0][cantidad]" class="form-control" placeholder="Cantidad" required min="1">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="productos[0][descuento]" class="form-control" placeholder="Descuento" step="0.01" min="0">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger btn-sm remove-producto" disabled>X</button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" id="add-producto">Agregar Producto</button>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Guardar Pedido</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productosContainer = document.getElementById('productos-list');
        const addProductoBtn = document.getElementById('add-producto');
        let productoCount = {{ old('productos') ? count(old('productos')) : 1 }};

        addProductoBtn.addEventListener('click', function() {
            const productoTemplate = document.querySelector('.producto-item').cloneNode(true);
            
            // Actualizar nombres de campos
            productoTemplate.querySelectorAll('select, input').forEach(input => {
                input.name = input.name.replace(/\[\d+\]/, `[${productoCount}]`);
                if (input.type !== 'button') {
                    input.value = '';
                    input.classList.remove('is-invalid');
                }
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
            });

            // Habilitar botón de eliminar
            productoTemplate.querySelector('.remove-producto').disabled = false;
            
            productosContainer.appendChild(productoTemplate);
            productoCount++;

            // Agregar evento para eliminar producto
            productoTemplate.querySelector('.remove-producto').addEventListener('click', function() {
                productoTemplate.remove();
            });
        });

        // Agregar evento de eliminar a los botones existentes
        document.querySelectorAll('.remove-producto').forEach(button => {
            if (!button.disabled) {
                button.addEventListener('click', function() {
                    button.closest('.producto-item').remove();
                });
            }
        });
    });
</script>
@endpush
