@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Cobertura de Venta Nacional (Charlas) - Admin</span>
                    <a href="{{ route('admin.charlas.create') }}" class="btn btn-primary btn-sm">Nueva Charla</a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Visitador</th>
                                    <th>Farmacia</th>
                                    <th>Zona</th>
                                    <th>Cant. Charlas</th>
                                    <th>Participantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($charlas as $charla)
                                    <tr>
                                        <td>{{ $charla->fecha->format('d/m/Y') }}</td>
                                        <td>{{ optional($charla->visitador)->nombre ?? '-' }}</td>
                                        <td>{{ optional($charla->cliente)->nombre_cliente ?? '-' }}</td>
                                        <td>{{ $charla->zona ?? '-' }}</td>
                                        <td>{{ $charla->cantidad_charlas }}</td>
                                        <td>{{ $charla->participantes }}</td>
                                        <td>
                                            <a href="{{ route('admin.charlas.edit', $charla) }}" class="btn btn-sm btn-primary">Editar</a>
                                            <form action="{{ route('admin.charlas.destroy', $charla) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta charla?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No hay charlas registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $charlas->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
