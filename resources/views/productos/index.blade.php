@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Lista de Productos</h3>
                    <a href="{{ route('productos.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Comisión (%)</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productos as $producto)
                                    <tr>
                                        <td>{{ $producto->id }}</td>
                                        <td>{{ $producto->nombre }}</td>
                                        <td>{{ $producto->comision }}%</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('productos.edit', ['producto' => $producto->id]) }}" 
                                                   class="btn btn-sm btn-primary me-2">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <form action="{{ route('productos.destroy', ['producto' => $producto->id]) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Está seguro que desea eliminar este producto?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No hay productos registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
