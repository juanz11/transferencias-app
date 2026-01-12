@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Editar transferencia #{{ $transferencia->transferencia_numero }}</h5>
                    <a href="{{ route('admin.pedidos.show', $transferencia) }}" class="btn btn-outline-secondary btn-sm">
                        Volver al detalle
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.pedidos.update', $transferencia) }}">
                        @csrf
                        @method('PUT')

                        {{-- Datos fijos --}}
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Visitador:</strong>
                                <p class="mb-0">{{ optional($transferencia->visitador)->nombre }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>Fecha de transferencia:</strong>
                                <p class="mb-0">
                                    {{ optional($transferencia->fecha_transferencia)->format('d/m/Y') }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <strong>Fecha de correo:</strong>
                                <p class="mb-0">
                                    {{ optional($transferencia->fecha_correo)->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="transferencia_numero" class="form-label">N° Transferencia</label>
                                <input
                                    type="text"
                                    name="transferencia_numero"
                                    id="transferencia_numero"
                                    class="form-control @error('transferencia_numero') is-invalid @enderror"
                                    value="{{ old('transferencia_numero', $transferencia->transferencia_numero) }}"
                                    required
                                >
                                @error('transferencia_numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        {{-- Código cliente con autocomplete + nombre actual --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="cliente_busqueda" class="form-label">Código cliente</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-warning text-dark">
                                        <i class="fas fa-hashtag"></i>
                                    </span>
                                    <input
                                        type="text"
                                        id="cliente_busqueda"
                                        class="form-control cliente-input @error('codigo_cliente') is-invalid @enderror"
                                        placeholder="Buscar cliente por nombre o código"
                                        value="{{ old('codigo_cliente', optional($transferencia->cliente)->codigo_cliente ? optional($transferencia->cliente)->nombre_cliente . ' - ' . optional($transferencia->cliente)->codigo_cliente : '') }}"
                                        autocomplete="off"
                                        required
                                    >
                                    <input
                                        type="hidden"
                                        name="codigo_cliente"
                                        class="codigo-cliente-hidden"
                                        value="{{ old('codigo_cliente', optional($transferencia->cliente)->codigo_cliente) }}"
                                    >
                                    @error('codigo_cliente')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Droguería actual</label>
                                <p class="form-control-plaintext mb-0">
                                    {{ optional(optional($transferencia->cliente)->drogueria)->nombre }}
                                </p>
                            </div>
                        </div>

                        {{-- Productos de la transferencia --}}
                        <hr>
                        <h5 class="mb-3">Pedidos de esta transferencia</h5>
                        <div class="row g-3 mb-3">
                            @foreach($transferencia->pedidos as $index => $pedido)
                                <div class="col-12 col-md-6 col-lg-4 producto-item">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body">
                                            <input type="hidden" name="pedido_ids[]" value="{{ $pedido->id }}">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold mb-1">Producto</label>
                                                <select name="producto_ids[]" class="form-select">
                                                    @foreach($productos as $producto)
                                                        <option value="{{ $producto->id }}" {{ $pedido->producto_id == $producto->id ? 'selected' : '' }}>
                                                            {{ $producto->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="input-group mb-2">
                                                <span class="input-group-text">Des</span>
                                                <input type="number" name="descuentos[]" class="form-control" min="0" max="100" value="{{ $pedido->descuento }}">
                                            </div>
                                            <div class="input-group">
                                                <span class="input-group-text">Unds</span>
                                                <input type="number" name="cantidades[]" class="form-control" min="1" value="{{ $pedido->cantidad }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                Guardar cambios
                            </button>
                            <a href="{{ route('admin.pedidos.show', $transferencia) }}" class="btn btn-secondary">
                                Cancelar
                            </a>
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
    $(function () {
        const clientes = {!! $clientes->map(function($cliente) {
            return [
                'label' => $cliente->nombre_cliente . ' - ' . $cliente->codigo_cliente,
                'value' => $cliente->codigo_cliente,
                'nombre' => $cliente->nombre_cliente,
            ];
        })->values()->toJson() !!};

        $('.cliente-input').autocomplete({
            source: clientes,
            minLength: 2,
            select: function (event, ui) {
                $(this).val(ui.item.label);              // muestra nombre + código
                $('.codigo-cliente-hidden').val(ui.item.value); // guarda solo el código
                return false;
            }
        }).autocomplete('instance')._renderItem = function (ul, item) {
            return $('<li>')
                .append('<div>' + item.nombre + '<br><small class="text-muted">' + item.value + '</small></div>')
                .appendTo(ul);
        };
    });
</script>
@endpush