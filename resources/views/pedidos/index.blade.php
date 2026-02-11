@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Reporte de Pedidos</div>

                <div class="card-body">
                    <form action="{{ route('pedidos.reporte') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha Inicio</label>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha Fin</label>
                                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="visitador_id">Visitador</label>
                                    <select name="visitador_id" id="visitador_id" class="form-control">
                                        <option value="">Todos los visitadores</option>
                                        @foreach($visitadores as $visitador)
                                            <option value="{{ $visitador->id }}">{{ $visitador->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="drogueria_id">Droguería</label>
                                    <select name="drogueria_id" id="drogueria_id" class="form-control">
                                        <option value="">Todas las droguerías</option>
                                        @foreach($drogerias as $drogueria)
                                            <option value="{{ $drogueria->id }}">{{ $drogueria->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="tipo_vista">Tipo de Vista</label>
                                    <select name="tipo_vista" id="tipo_vista" class="form-control">
                                        <option value="individual">Productos Individuales</option>
                                        <option value="agrupado">Productos Agrupados</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="descuento">Descuento (%)</label>
                                    <select name="descuento" id="descuento" class="form-control">
                                        <option value="">Todos los descuentos</option>
                                        <option value="0">0%</option>
                                        <option value="5">5%</option>
                                        <option value="10">10%</option>
                                        <option value="15">15%</option>
                                        <option value="20">20%</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">Generar Reporte</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
