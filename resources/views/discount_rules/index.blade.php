@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-md-6">
            <h2>{{ __('Reglas de descuento') }}</h2>
        </div>
        <div class="col-md-6 text-end">
            <form action="{{ route('discount_rules.index') }}" method="GET" class="d-inline-flex mb-3 w-100">
                <input type="text" name="search" class="form-control me-2" placeholder="Buscar por ID o nombre de producto" value="{{ $search ?? '' }}">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </form>
            <a href="{{ route('discount_rules.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('Nueva regla') }}
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Lista de reglas</h3>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>Droguería</th>
                                    <th>Menor</th>
                                    <th>Medio</th>
                                    <th>Mayor</th>
                                    <th>Activa</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rules as $rule)
                                    <tr>
                                        <td>{{ $rule->id }}</td>
                                        <td>
                                            {{ $rule->producto->nombre ?? 'N/A' }}
                                            <div class="text-muted"><small>ID: {{ $rule->producto_id }}</small></div>
                                        </td>
                                        <td>
                                            {{ $rule->drogueria->nombre ?? 'Todas' }}
                                            @if($rule->drogueria_id)
                                                <div class="text-muted"><small>ID: {{ $rule->drogueria_id }}</small></div>
                                            @endif
                                        </td>
                                        <td>{{ $rule->min_qty_low }} => {{ $rule->pct_low }}%</td>
                                        <td>{{ $rule->min_qty_mid }} => {{ $rule->pct_mid }}%</td>
                                        <td>{{ $rule->min_qty_high }} => {{ $rule->pct_high }}%</td>
                                        <td>
                                            @if($rule->is_active)
                                                <span class="badge bg-success">Sí</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('discount_rules.edit', $rule) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <form action="{{ route('discount_rules.destroy', $rule) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro que desea eliminar esta regla?')">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No hay reglas registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($rules->hasPages())
                        <div class="card-footer">
                            {{ $rules->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
