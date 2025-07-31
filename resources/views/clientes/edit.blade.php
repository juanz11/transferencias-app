@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Editar Cliente') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('clientes.update', $cliente) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group row mb-3">
                            <label for="codigo_cliente" class="col-md-4 col-form-label text-md-right">{{ __('Código Cliente') }}</label>
                            <div class="col-md-6">
                                <input id="codigo_cliente" type="text" class="form-control @error('codigo_cliente') is-invalid @enderror" 
                                    name="codigo_cliente" value="{{ old('codigo_cliente', $cliente->codigo_cliente) }}" required autocomplete="codigo_cliente" autofocus>
                                @error('codigo_cliente')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="nombre_cliente" class="col-md-4 col-form-label text-md-right">{{ __('Nombre Cliente') }}</label>
                            <div class="col-md-6">
                                <input id="nombre_cliente" type="text" class="form-control @error('nombre_cliente') is-invalid @enderror" 
                                    name="nombre_cliente" value="{{ old('nombre_cliente', $cliente->nombre_cliente) }}" required autocomplete="nombre_cliente">
                                @error('nombre_cliente')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="drogueria" class="col-md-4 col-form-label text-md-right">{{ __('Droguería') }}</label>
                            <div class="col-md-6">
                                <select id="drogueria" class="form-control @error('drogueria') is-invalid @enderror" 
                                    name="drogueria" required>
                                    <option value="">Seleccione una droguería</option>
                                    @foreach($droguerias as $drogueria)
                                        <option value="{{ $drogueria->id }}" {{ (old('drogueria', $cliente->drogueria) == $drogueria->id) ? 'selected' : '' }}>
                                            {{ $drogueria->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('drogueria')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Actualizar Cliente') }}
                                </button>
                                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                                    {{ __('Cancelar') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
