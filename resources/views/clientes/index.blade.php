@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-md-6">
            <h2>{{ __('Clientes') }}</h2>
        </div>
        <div class="col-md-6 text-end">
            <form action="{{ route('clientes.index') }}" method="GET" class="d-inline-flex mb-3 w-100">
                <input type="text" name="search" class="form-control me-2" placeholder="Buscar por código o nombre" value="{{ $search ?? '' }}">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </form>
            <a href="{{ route('clientes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('Nuevo Cliente') }}
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <colgroup>
                        <col style="width: 15%;">
                        <col style="width: 35%;">
                        <col style="width: 25%;">
                        <col style="width: 15%;">
                        <col style="width: 10%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>{{ __('Código') }}</th>
                            <th>{{ __('Nombre') }}</th>
                            <th>{{ __('Droguería') }}</th>
                            <th>{{ __('Zona') }}</th>
                            <th>{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $cliente)
                            <tr>
                                <td>{{ $cliente->codigo_cliente }}</td>
                                <td>{{ $cliente->nombre_cliente }}</td>
                                <td>{{ $cliente->drogueria->nombre ?? 'N/A' }}</td>
                                <td>{{ $cliente->zona ?? '-' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-sm btn-primary">
                                            {{ __('Editar') }}
                                        </a>
                                        <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro que desea eliminar este cliente?')">
                                                {{ __('Eliminar') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $clientes->appends(['search' => $search])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
