@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<style>
    .ui-autocomplete {
        max-height: 200px;
        overflow-y: auto;
        overflow-x: hidden;
        z-index: 1000;
        background: white !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .ui-menu-item {
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
        background: white !important;
    }

    .ui-menu-item:hover {
        background-color: #f8f9fa !important;
        cursor: pointer;
    }

    .ui-state-active,
    .ui-widget-content .ui-state-active {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: white !important;
        margin: 0 !important;
    }

    .ui-menu {
        padding: 0 !important;
        border: none !important;
        background: white !important;
    }

    .ui-widget.ui-widget-content {
        border: 1px solid #ced4da;
    }

    .cliente-input {
        width: 100%;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }

    .cliente-input:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card shadow-lg card-custom-bg">
                <div class="card-body p-4">
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

                    <form action="{{ route('visitador.pedidos.store') }}" method="POST" id="pedidoForm">
                        @csrf

                        <div class="mb-4">
                            <h5 class="mb-1">Visitador</h5>
                            <p class="mb-0"><strong>{{ $visitador->nombre }}</strong> ({{ $visitador->email }})</p>
                        </div>

                        <input type="hidden" name="fecha_correo" id="fecha_correo" value="{{ date('Y-m-d') }}">
                        <input type="hidden" name="fecha_transferencia" id="fecha_transferencia" value="{{ date('Y-m-d') }}">

                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label for="transferencia_numero" class="form-label">Número de Transferencia</label>
                                <input type="text" name="transferencia_numero" id="transferencia_numero"
                                       class="form-control @error('transferencia_numero') is-invalid @enderror"
                                       value="{{ old('transferencia_numero') }}" required>
                                @error('transferencia_numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-center d-block fw-bold">Código Cliente</label>
                            <div class="input-group input-group-lg justify-content-center">
                                <span class="input-group-text bg-warning text-dark">
                                    <i class="fas fa-hashtag"></i>
                                </span>
                                <input type="text" class="form-control cliente-input @error('codigo_cliente') is-invalid @enderror"
                                       placeholder="Buscar cliente por nombre o código"
                                       required
                                       autocomplete="off">
                                <input type="hidden" name="codigo_cliente" class="codigo-cliente-hidden" value="{{ old('codigo_cliente') }}">
                                @error('codigo_cliente')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Productos</h5>
                            <button type="button" class="btn btn-primary btn-sm" id="add-producto">Agregar producto</button>
                        </div>

                        <div id="productos-list" class="row g-3">
                            @if(old('productos'))
                                @foreach(old('productos') as $key => $oldProducto)
                                    <div class="col-12 col-md-6 col-lg-4 producto-item">
                                        <div class="card h-100 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0">Producto</h6>
                                                    <button type="button" class="btn btn-danger btn-sm remove-producto" {{ $key == 0 ? 'disabled' : '' }}>X</button>
                                                </div>
                                                <select name="productos[{{ $key }}][id]" class="form-select mb-3 @error('productos.'.$key.'.id') is-invalid @enderror" required>
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
                                                <div class="input-group mb-2">
                                                    <span class="input-group-text">Des</span>
                                                    <input type="number" name="productos[{{ $key }}][descuento]"
                                                           class="form-control @error('productos.'.$key.'.descuento') is-invalid @enderror"
                                                           value="{{ $oldProducto['descuento'] }}" placeholder="%" step="0.01" min="0">
                                                    @error('productos.'.$key.'.descuento')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="input-group">
                                                    <span class="input-group-text">Unds</span>
                                                    <input type="number" name="productos[{{ $key }}][cantidad]"
                                                           class="form-control @error('productos.'.$key.'.cantidad') is-invalid @enderror"
                                                           value="{{ $oldProducto['cantidad'] }}" placeholder="Cantidad" required min="1">
                                                    @error('productos.'.$key.'.cantidad')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-12 col-md-6 col-lg-4 producto-item">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">Producto</h6>
                                                <button type="button" class="btn btn-danger btn-sm remove-producto" disabled>X</button>
                                            </div>
                                            <select name="productos[0][id]" class="form-select mb-3" required>
                                                <option value="">Seleccione un producto</option>
                                                @foreach($productos as $producto)
                                                    <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group mb-2">
                                                <span class="input-group-text">Des</span>
                                                <input type="number" name="productos[0][descuento]" class="form-control" placeholder="%" step="0.01" min="0">
                                            </div>
                                            <div class="input-group">
                                                <span class="input-group-text">Unds</span>
                                                <input type="number" name="productos[0][cantidad]" class="form-control" placeholder="Cantidad" required min="1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary px-5">Guardar como pendiente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productosContainer = document.getElementById('productos-list');
        const addProductoBtn = document.getElementById('add-producto');
        let productoCount = {{ old('productos') ? count(old('productos')) : 1 }};

        addProductoBtn.addEventListener('click', function() {
            const template = document.querySelector('.producto-item').cloneNode(true);
            template.querySelectorAll('select, input').forEach(input => {
                input.name = input.name.replace(/\[\d+\]/, '[' + productoCount + ']');
                if (input.type !== 'button') {
                    input.value = '';
                    input.classList.remove('is-invalid');
                }
            });
            const removeBtn = template.querySelector('.remove-producto');
            removeBtn.disabled = false;
            removeBtn.addEventListener('click', function() {
                this.closest('.producto-item').remove();
            });
            productosContainer.appendChild(template);
            productoCount++;
        });

        document.querySelectorAll('.remove-producto').forEach(button => {
            if (!button.disabled) {
                button.addEventListener('click', function() {
                    this.closest('.producto-item').remove();
                });
            }
        });

        const clientes = @json($clientes->map(function($cliente) {
            return [
                'label' => $cliente->nombre_cliente . ' - ' . $cliente->codigo_cliente,
                'value' => $cliente->codigo_cliente,
                'nombre' => $cliente->nombre_cliente
            ];
        }));

        $('.cliente-input').autocomplete({
            source: clientes,
            minLength: 2,
            select: function(event, ui) {
                event.preventDefault();
                $(this).val(ui.item.label);
                $('.codigo-cliente-hidden').val(ui.item.value);
            }
        }).autocomplete('instance')._renderItem = function(ul, item) {
            return $('<li>')
                .append('<div>' + item.nombre + '<br><small class="text-muted">' + item.value + '</small></div>')
                .appendTo(ul);
        };
    });
</script>
@endpush
