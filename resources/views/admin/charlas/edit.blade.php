@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Editar Charla - Admin</div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.charlas.update', $charla) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group row mb-3">
                            <label for="fecha" class="col-md-4 col-form-label text-md-right">Fecha</label>
                            <div class="col-md-6">
                                <input id="fecha" type="date" class="form-control @error('fecha') is-invalid @enderror" name="fecha" value="{{ old('fecha', $charla->fecha->format('Y-m-d')) }}" required>
                                @error('fecha')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="visitador_id" class="col-md-4 col-form-label text-md-right">Visitador</label>
                            <div class="col-md-6">
                                <select id="visitador_id" class="form-control @error('visitador_id') is-invalid @enderror" name="visitador_id">
                                    <option value="">Seleccione un visitador</option>
                                    @foreach($visitadores as $visitador)
                                        <option value="{{ $visitador->id }}" {{ old('visitador_id', $charla->visitador_id) == $visitador->id ? 'selected' : '' }}>{{ $visitador->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('visitador_id')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="cliente_id" class="col-md-4 col-form-label text-md-right">Farmacia</label>
                            <div class="col-md-6">
                                <select id="cliente_id" class="form-control @error('cliente_id') is-invalid @enderror" name="cliente_id" required>
                                    <option value="">Seleccione una farmacia</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" data-zona="{{ $cliente->zona ?? '' }}" {{ old('cliente_id', $charla->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nombre_cliente }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cliente_id')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="zona" class="col-md-4 col-form-label text-md-right">Zona</label>
                            <div class="col-md-6">
                                <input id="zona" type="text" class="form-control @error('zona') is-invalid @enderror" name="zona" value="{{ old('zona', $charla->zona) }}" readonly>
                                @error('zona')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="cantidad_charlas" class="col-md-4 col-form-label text-md-right">Cantidad de Charlas</label>
                            <div class="col-md-6">
                                <input id="cantidad_charlas" type="number" min="0" class="form-control @error('cantidad_charlas') is-invalid @enderror" name="cantidad_charlas" value="{{ old('cantidad_charlas', $charla->cantidad_charlas) }}" required>
                                @error('cantidad_charlas')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="participantes" class="col-md-4 col-form-label text-md-right">Participantes</label>
                            <div class="col-md-6">
                                <input id="participantes" type="number" min="0" class="form-control @error('participantes') is-invalid @enderror" name="participantes" value="{{ old('participantes', $charla->participantes) }}" required>
                                @error('participantes')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                                <a href="{{ route('admin.charlas.index') }}" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const clienteSelect = document.getElementById('cliente_id');
    const zonaInput = document.getElementById('zona');

    function actualizarZona() {
        const selected = clienteSelect.options[clienteSelect.selectedIndex];
        const zona = selected.getAttribute('data-zona') || '';
        zonaInput.value = zona;
    }

    clienteSelect.addEventListener('change', actualizarZona);
    actualizarZona();
});
</script>
@endpush
