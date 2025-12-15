@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
    
    /* Custom Divider Style */
    .divider-custom {
        display: flex;
        align-items: center;
        text-align: center;
        font-weight: 600;
        font-size: 1.1rem;
        color: #495057;
        margin: 2rem 0 1rem 0;
    }
    
    .divider-custom::before,
    .divider-custom::after {
        content: '';
        flex: 1;
        border-bottom: 2px solid #dee2e6;
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
    
    .card-custom-bg .form-text {
        color: #e0e0e0 !important;
    }
    
    /* Modal styling */
    #confirmacionModal .modal-body p,
    #confirmacionModal .modal-body strong,
    #confirmacionModal .modal-body td,
    #confirmacionModal .modal-body th {
        color: #212529 !important;
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

                    <form action="{{ route('transferencias.pedidos.store') }}" method="POST" id="pedidoForm">
                        @csrf
                        
                        <!-- Divider Visitador -->
                        <div class="divider-custom mb-4">
                            <i class="fas fa-briefcase me-2"></i>Visitador
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label for="visitador_id" class="form-label text-center d-block fw-bold">Seleccione un visitador</label>
                                <div class="input-group input-group-lg justify-content-center">
                                    <span class="input-group-text bg-primary text-white">
                                        <i class="fas fa-briefcase"></i>
                                    </span>
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
                            </div>
                        </div>

                        <!-- Divider Fechas -->
                        <div class="divider-custom mb-4">
                            <i class="fas fa-calendar me-2"></i>Fechas y Número de Transferencia
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label for="fecha_correo" class="form-label text-center d-block fw-bold">Fecha de Correo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-info text-white">
                                        <i class="fas fa-envelope-open"></i>
                                    </span>
                                    <input type="date" name="fecha_correo" id="fecha_correo" 
                                        class="form-control @error('fecha_correo') is-invalid @enderror" 
                                        value="{{ old('fecha_correo') }}" required
                                        max="{{ date('Y-m-d') }}">
                                    @error('fecha_correo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="fecha_transferencia" class="form-label text-center d-block fw-bold">Fecha de Transferencia</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white">
                                        <i class="fas fa-arrow-right-arrow-left"></i>
                                    </span>
                                    <input type="date" name="fecha_transferencia" id="fecha_transferencia" 
                                        class="form-control @error('fecha_transferencia') is-invalid @enderror" 
                                        value="{{ old('fecha_transferencia') }}" required
                                        max="{{ date('Y-m-d') }}">
                                    @error('fecha_transferencia')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="transferencia_numero" class="form-label text-center d-block fw-bold">Número de Transferencia</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-danger text-white">
                                        <i class="fas fa-hashtag"></i>
                                    </span>
                                    <input type="text" name="transferencia_numero" id="transferencia_numero" 
                                        class="form-control fw-bold @error('transferencia_numero') is-invalid @enderror" 
                                        value="{{ old('transferencia_numero') }}" required
                                        placeholder="Ingrese el Nº (8501 en adelante)">
                                    @error('transferencia_numero')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="form-text small">Ingrese un número único entre 8501 y 9000 que no esté usado.</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Divider Cliente -->
                        <div class="divider-custom mb-4">
                            <i class="fas fa-user-tie me-2"></i>Datos del Cliente
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
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
                        </div>

                        <!-- Divider Productos -->
                        <div class="divider-custom mb-4">
                            <i class="fas fa-box me-2"></i>Productos
                        </div>
                        
                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-primary btn-lg" id="add-producto">
                                <i class="fas fa-plus me-2"></i>Agregar Producto
                            </button>
                        </div>

                        <div id="productos-list" class="row g-3">
                                @if(old('productos'))
                                    @foreach(old('productos') as $key => $oldProducto)
                                        <div class="col-12 col-md-6 col-lg-4 producto-item">
                                            <div class="card h-100 shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <h6 class="mb-0"><i class="fas fa-box text-primary me-2"></i>Producto</h6>
                                                        <button type="button" class="btn btn-danger btn-sm remove-producto" {{ $key == 0 ? 'disabled' : '' }}>
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    <select name="productos[{{ $key }}][id]" class="form-select producto-select mb-3 @error('productos.'.$key.'.id') is-invalid @enderror" required>
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
                                                        <span class="input-group-text">Des (porcentaje %)</span>
                                                        <input type="number" name="productos[{{ $key }}][descuento]" 
                                                            class="form-control @error('productos.'.$key.'.descuento') is-invalid @enderror"
                                                            value="{{ $oldProducto['descuento'] }}"
                                                            placeholder="%" step="0.01" min="0">
                                                        @error('productos.'.$key.'.descuento')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Unds</span>
                                                        <input type="number" name="productos[{{ $key }}][cantidad]" 
                                                            class="form-control @error('productos.'.$key.'.cantidad') is-invalid @enderror"
                                                            value="{{ $oldProducto['cantidad'] }}" 
                                                            placeholder="Cantidad" required min="1">
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
                                                    <h6 class="mb-0"><i class="fas fa-box text-primary me-2"></i>Producto</h6>
                                                    <button type="button" class="btn btn-danger btn-sm remove-producto" disabled>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                <select name="productos[0][id]" class="form-select producto-select mb-3" required>
                                                    <option value="">Seleccione un producto</option>
                                                    @foreach($productos as $producto)
                                                        <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="input-group mb-2">
                                                    <span class="input-group-text">Des (porcentaje %) </span>
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
                            <button type="button" class="btn btn-primary btn-lg px-5" id="confirmarPedido">
                                <i class="fas fa-check me-2"></i>Registrar
                            </button>
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
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

        function initializeSelect2(element) {
            if (!$(element).data('select2')) {
                $(element).select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Seleccione un producto',
                    allowClear: true,
                    width: '100%'
                });
            }
        }

        $('#visitador_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Seleccione un visitador',
            allowClear: true
        });

        $('.producto-select').each(function() {
            initializeSelect2(this);
        });

        addProductoBtn.addEventListener('click', function() {
            // Usar siempre el primer .producto-item como plantilla base
            const baseItem = document.querySelector('.producto-item');

            if (!baseItem) return;

            // Asegurar que la plantilla NO tenga select2 renderizado
            const baseSelect = baseItem.querySelector('.producto-select');
            if (baseSelect && $(baseSelect).data('select2')) {
                $(baseSelect).select2('destroy');
            }
            // Eliminar cualquier contenedor select2 clonado previamente
            baseItem.querySelectorAll('.select2, .select2-container').forEach(el => el.remove());

            // Clonar la tarjeta limpia
            const productoTemplate = baseItem.cloneNode(true);

            // Actualizar nombres de campos e indices
            productoTemplate.querySelectorAll('select, input').forEach(input => {
                if (input.name) {
                    // Usar concatenación de strings en lugar de template literal para evitar conflictos de parsing con Blade
                    input.name = input.name.replace(/\[\d+\]/, '[' + productoCount + ']');
                }
                if (input.type !== 'button') {
                    input.value = '';
                    input.classList.remove('is-invalid');
                }
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
            });

            // Habilitar botón de eliminar en la nueva fila
            const removeBtn = productoTemplate.querySelector('.remove-producto');
            if (removeBtn) {
                removeBtn.disabled = false;
            }

            // Agregar el nuevo producto al contenedor
            productosContainer.appendChild(productoTemplate);

            // Inicializar select2 SOLO en el select de la nueva fila
            const newSelect = productoTemplate.querySelector('.producto-select');
            if (newSelect) {
                initializeSelect2(newSelect);
            }

            // Volver a inicializar select2 en el primer item (por si se destruyó)
            if (baseSelect) {
                initializeSelect2(baseSelect);
            }

            // Incrementar el contador
            productoCount++;

            // Agregar evento para eliminar producto en la nueva fila
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    const select = this.closest('.producto-item').querySelector('.producto-select');
                    if (select && $(select).data('select2')) {
                        $(select).select2('destroy');
                    }
                    this.closest('.producto-item').remove();
                });
            }
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
