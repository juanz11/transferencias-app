@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Crear Regla de Descuento</h3>
                </div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('discount_rules.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="producto_id" class="form-label">Producto</label>
                                <select id="producto_id" name="producto_id" class="form-select @error('producto_id') is-invalid @enderror" required>
                                    <option value="">Seleccione un producto</option>
                                    @foreach($productos as $producto)
                                        <option value="{{ $producto->id }}" {{ (string)old('producto_id') === (string)$producto->id ? 'selected' : '' }}>
                                            {{ $producto->nombre }} (ID: {{ $producto->id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('producto_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="drogueria_id" class="form-label">Droguería</label>
                                <select id="drogueria_id" name="drogueria_id" class="form-select @error('drogueria_id') is-invalid @enderror">
                                    <option value="">Todas (Regla global)</option>
                                    @foreach($droguerias as $drogueria)
                                        <option value="{{ $drogueria->id }}" {{ (string)old('drogueria_id') === (string)$drogueria->id ? 'selected' : '' }}>
                                            {{ $drogueria->nombre }} (ID: {{ $drogueria->id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('drogueria_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Menor</label>
                                <div class="input-group">
                                    <span class="input-group-text">Min</span>
                                    <input type="number" min="0" class="form-control @error('min_qty_low') is-invalid @enderror" name="min_qty_low" value="{{ old('min_qty_low', 0) }}" required>
                                </div>
                                @error('min_qty_low')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="input-group mt-2">
                                    <span class="input-group-text">%</span>
                                    <input type="number" step="0.01" min="0" max="100" class="form-control @error('pct_low') is-invalid @enderror" name="pct_low" value="{{ old('pct_low', 0) }}" required>
                                </div>
                                @error('pct_low')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Medio</label>
                                <div class="input-group">
                                    <span class="input-group-text">Min</span>
                                    <input type="number" min="0" class="form-control @error('min_qty_mid') is-invalid @enderror" name="min_qty_mid" value="{{ old('min_qty_mid', 0) }}" required>
                                </div>
                                @error('min_qty_mid')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="input-group mt-2">
                                    <span class="input-group-text">%</span>
                                    <input type="number" step="0.01" min="0" max="100" class="form-control @error('pct_mid') is-invalid @enderror" name="pct_mid" value="{{ old('pct_mid', 0) }}" required>
                                </div>
                                @error('pct_mid')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Mayor</label>
                                <div class="input-group">
                                    <span class="input-group-text">Min</span>
                                    <input type="number" min="0" class="form-control @error('min_qty_high') is-invalid @enderror" name="min_qty_high" value="{{ old('min_qty_high', 0) }}" required>
                                </div>
                                @error('min_qty_high')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="input-group mt-2">
                                    <span class="input-group-text">%</span>
                                    <input type="number" step="0.01" min="0" max="100" class="form-control @error('pct_high') is-invalid @enderror" name="pct_high" value="{{ old('pct_high', 0) }}" required>
                                </div>
                                @error('pct_high')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" {{ old('is_active', 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Activa</label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('discount_rules.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
