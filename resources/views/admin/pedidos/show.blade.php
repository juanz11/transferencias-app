@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detalle de transferencia #{{ $transferencia->transferencia_numero }}</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.pedidos.edit', $transferencia) }}" class="btn btn-primary btn-sm">Editar</a>
                        <a href="{{ route('admin.pedidos.pendientes') }}" class="btn btn-outline-secondary btn-sm">Volver a pendientes</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Visitador:</strong>
                            <p class="mb-0">{{ optional($transferencia->visitador)->nombre }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Cliente:</strong>
                            <p class="mb-0">{{ optional($transferencia->cliente)->nombre_cliente }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Código cliente:</strong>
                            <p class="mb-0">{{ optional($transferencia->cliente)->codigo_cliente }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Droguería:</strong>
                            <p class="mb-0">{{ $drogueria ? $drogueria->nombre : '' }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Fecha de transferencia:</strong>
                            <p class="mb-0">{{ optional($transferencia->fecha_transferencia)->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Fecha de correo:</strong>
                            <p class="mb-0">{{ optional($transferencia->fecha_correo)->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Estado:</strong>
                            <p class="mb-0">{{ $transferencia->confirmada ? 'Confirmada' : 'Pendiente de aprobación' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Pedidos de esta transferencia</h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="sort-product-asc">Producto A-Z</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="sort-product-desc">Producto Z-A</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="sort-cantidad-asc">Cantidad ↑</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="sort-cantidad-desc">Cantidad ↓</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0" id="tabla-pedidos-transferencia">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Descuento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transferencia->pedidos as $pedido)
                                    <tr>
                                        <td>{{ optional($pedido->producto)->nombre }}</td>
                                        <td>{{ $pedido->cantidad }}</td>
                                        <td>{{ $pedido->descuento }}%</td>
                                        <td>{{ ucfirst($pedido->estado) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($transferencia->pedidos->where('estado', 'pendiente')->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Acciones</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.pedidos.cambiar-estado', $transferencia) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="estado" value="aprobado">
                        <button type="submit" class="btn btn-success" onclick="return confirm('¿Aprobar y confirmar todos los pedidos pendientes de esta transferencia?')">
                            Aprobar y confirmar pedidos
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const table = document.getElementById('tabla-pedidos-transferencia');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        function normalizeText(text) {
            return (text || '').toString().trim().toLowerCase();
        }

        function sortRows(comparator) {
            const rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort(comparator);
            rows.forEach(row => tbody.appendChild(row));
        }

        function getCellText(row, index) {
            const cell = row.children[index];
            return cell ? cell.textContent : '';
        }

        function getCellNumber(row, index) {
            const value = parseFloat(getCellText(row, index).replace(',', '.'));
            return Number.isFinite(value) ? value : 0;
        }

        const sortProductAsc = document.getElementById('sort-product-asc');
        const sortProductDesc = document.getElementById('sort-product-desc');
        const sortCantidadAsc = document.getElementById('sort-cantidad-asc');
        const sortCantidadDesc = document.getElementById('sort-cantidad-desc');

        if (sortProductAsc) {
            sortProductAsc.addEventListener('click', function () {
                sortRows((a, b) => normalizeText(getCellText(a, 0)).localeCompare(normalizeText(getCellText(b, 0))));
            });
        }

        if (sortProductDesc) {
            sortProductDesc.addEventListener('click', function () {
                sortRows((a, b) => normalizeText(getCellText(b, 0)).localeCompare(normalizeText(getCellText(a, 0))));
            });
        }

        if (sortCantidadAsc) {
            sortCantidadAsc.addEventListener('click', function () {
                sortRows((a, b) => getCellNumber(a, 1) - getCellNumber(b, 1));
            });
        }

        if (sortCantidadDesc) {
            sortCantidadDesc.addEventListener('click', function () {
                sortRows((a, b) => getCellNumber(b, 1) - getCellNumber(a, 1));
            });
        }
    })();
</script>
@endpush
