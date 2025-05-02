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
    
    .select2-container {
        width: 100% !important;
    }
    .select2-selection,
    .select2-selection--single,
    .select2-container--default .select2-selection--single,
    .select2-container--bootstrap-5 .select2-selection {
        height: 38px !important;
        padding: 5px !important;
        background-color: #ffffff !important;
        background: #ffffff !important;
    }
    .select2-container--default .select2-selection--single,
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
    }

    .select2-dropdown {
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
    }

    .select2-results__option {
        padding: 0.375rem 0.75rem !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd !important;
    }
</style>
@endsection

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
                            <label class="form-label">Cliente</label>
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

                    <div class="row mb-3">
                      
                        
                        <div class="col-md-4">
                            <label for="fecha_transferencia" class="form-label">Fecha de Transferencia</label>
                            <input type="date" name="fecha_transferencia" id="fecha_transferencia" 
                                class="form-control @error('fecha_transferencia') is-invalid @enderror" 
                                value="{{ old('fecha_transferencia') }}" required
                                max="{{ date('Y-m-d') }}">
                            @error('fecha_transferencia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                          <div class="col-md-4">
                            <label for="fecha_correo" class="form-label">Fecha de Correo</label>
                            <input type="date" name="fecha_correo" id="fecha_correo" 
                                class="form-control @error('fecha_correo') is-invalid @enderror" 
                            value="{{ old('fecha_correo') }}" required
                                max="{{ date('Y-m-d') }}">
                            @error('fecha_correo')
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
                                            <input type="number" name="productos[0][descuento]" class="form-control" placeholder="Descuento" step="0.01" min="0">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="productos[0][cantidad]" class="form-control" placeholder="Cantidad" required min="1">
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
                        <button type="button" class="btn btn-primary" id="confirmarPedido">Confirmar</button>
                    </div>
                </form>

                <!-- Modal de Confirmación -->
                <div class="modal fade" id="confirmacionModal" tabindex="-1" aria-labelledby="confirmacionModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmacionModalLabel">Confirmar Pedido</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Visitador:</strong>
                                        <p id="modal-visitador"></p>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Cliente:</strong>
                                        <p id="modal-cliente"></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong>Fecha de Transferencia:</strong>
                                        <p id="modal-fecha-transferencia"></p>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Fecha de Correo:</strong>
                                        <p id="modal-fecha-correo"></p>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Número de Transferencia:</strong>
                                        <p id="modal-transferencia-numero"></p>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Cantidad</th>
                                                <th>Descuento</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modal-productos">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="confirmarFinal">Aceptar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
    $(document).ready(function() {
        const fechaCorreo = document.getElementById('fecha_correo');
        const fechaTransferencia = document.getElementById('fecha_transferencia');

        function validarFechas() {
            if (fechaCorreo.value && fechaTransferencia.value) {
                if (fechaTransferencia.value > fechaCorreo.value) {
                    fechaTransferencia.setCustomValidity('La fecha de transferencia no puede ser posterior a la fecha de correo');
                } else {
                    fechaTransferencia.setCustomValidity('');
                }
            }
        }

        fechaCorreo.addEventListener('change', validarFechas);
        fechaTransferencia.addEventListener('change', validarFechas);

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
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>")
                .append("<div>" + item.nombre + "<br><small class='text-muted'>" + item.value + "</small></div>")
                .appendTo(ul);
        };

        const productosContainer = document.getElementById('productos-list');
        const addProductoBtn = document.getElementById('add-producto');
        let productoCount = {{ old('productos') ? count(old('productos')) : 1 }};

        addProductoBtn.addEventListener('click', function() {
            const productoTemplate = document.querySelector('.producto-item').cloneNode(true);
            
            // Destruir Select2 existente si existe
            const oldSelect = productoTemplate.querySelector('.producto-select');
            if ($(oldSelect).data('select2')) {
                $(oldSelect).select2('destroy');
            }
            
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

            // Inicializar Select2 en el nuevo select
            //initializeSelect2(productoTemplate.querySelector('.producto-select'));
            
            productoCount++;

            // Agregar evento para eliminar producto
            productoTemplate.querySelector('.remove-producto').addEventListener('click', function() {
                const select = this.closest('.producto-item').querySelector('.producto-select');
                if ($(select).data('select2')) {
                    $(select).select2('destroy');
                }
                this.closest('.producto-item').remove();
            });
        });

        // Agregar evento de eliminar a los botones existentes
        document.querySelectorAll('.remove-producto').forEach(button => {
            if (!button.disabled) {
                button.addEventListener('click', function() {
                    const select = this.closest('.producto-item').querySelector('.producto-select');
                    if ($(select).data('select2')) {
                        $(select).select2('destroy');
                    }
                    this.closest('.producto-item').remove();
                });
            }
        });

        // Evento para mostrar el modal de confirmación
        $('#confirmarPedido').click(function() {
            // Validar el formulario antes de mostrar el modal
            if (!document.getElementById('pedidoForm').checkValidity()) {
                document.getElementById('pedidoForm').reportValidity();
                return;
            }

            // Llenar la información del modal
            $('#modal-visitador').text($('#visitador_id option:selected').text());
            $('#modal-cliente').text($('.cliente-input').val());
            $('#modal-fecha-transferencia').text($('#fecha_transferencia').val());
            $('#modal-fecha-correo').text($('#fecha_correo').val());
            $('#modal-transferencia-numero').text($('#transferencia_numero').val());

            // Limpiar y llenar la tabla de productos
            $('#modal-productos').empty();
            $('.producto-item').each(function() {
                const productoNombre = $(this).find('.producto-select option:selected').text();
                const cantidad = $(this).find('input[name$="[cantidad]"]').val();
                const descuento = $(this).find('input[name$="[descuento]"]').val() || '0';

                $('#modal-productos').append(`
                    <tr>
                        <td>${productoNombre}</td>
                        <td>${cantidad}</td>
                        <td>${descuento}%</td>
                    </tr>
                `);
            });

            // Mostrar el modal
            new bootstrap.Modal(document.getElementById('confirmacionModal')).show();
        });

        // Evento para confirmar y enviar el formulario
        $('#confirmarFinal').click(function() {
            document.getElementById('pedidoForm').submit();
        });
    });
</script>
@endpush
