@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Lista de Visitadores</h3>
                    <a href="{{ route('visitadores.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Visitador
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
                                    <th>Email</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($visitadores as $visitador)
                                    <tr>
                                        <td>{{ $visitador->id }}</td>
                                        <td>{{ $visitador->nombre }}</td>
                                        <td>{{ $visitador->email }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('visitadores.edit', ['visitador' => $visitador->id]) }}" 
                                                   class="btn btn-sm btn-primary me-2">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <form action="{{ route('visitadores.destroy', ['visitador' => $visitador->id]) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      style="display: none;"
                                                      onsubmit="return confirm('¿Está seguro que desea eliminar este visitador?');">
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
                                        <td colspan="4" class="text-center">No hay visitadores registrados.</td>
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
