@extends('layouts.app')

@section('styles')
<style>
    .pedido-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .producto-nombre {
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Transferencia Confirmada</h3>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <strong>Fecha Transferencia:</strong>
                    <p>{{ $transferenciaConfirmada->transferencia->fecha_transferencia->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-4">
                    <strong>N° Transferencia:</strong>
                    <p>{{ $transferenciaConfirmada->transferencia->transferencia_numero }}</p>
                </div>
                <div class="col-md-4">
                    <strong>Visitador:</strong>
                    <p>{{ $transferenciaConfirmada->transferencia->visitador->nombre ?? 'Sin Visitador' }}</p>
                </div>
            </div>

            <form action="{{ route('transferencias.confirmados.update', $transferenciaConfirmada->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    @foreach($transferenciaConfirmada->pedidosConfirmados as $pedido)
                        <div class="col-md-6">
                            <div class="pedido-card">
                                <div class="producto-nombre">
                                    {{ $pedido->producto->nombre }}
                                </div>
                                <input type="hidden" name="pedidos[{{ $loop->index }}][id]" value="{{ $pedido->id }}">
                                
                                <div class="form-group">
                                    <label>Cantidad:</label>
                                    <input type="number" 
                                           name="pedidos[{{ $loop->index }}][cantidad]" 
                                           class="form-control @error('pedidos.' . $loop->index . '.cantidad') is-invalid @enderror" 
                                           value="{{ old('pedidos.' . $loop->index . '.cantidad', $pedido->cantidad) }}"
                                           min="1"
                                           required>
                                    @error('pedidos.' . $loop->index . '.cantidad')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label>Descuento (%):</label>
                                    <input type="number" 
                                           name="pedidos[{{ $loop->index }}][descuento]" 
                                           class="form-control @error('pedidos.' . $loop->index . '.descuento') is-invalid @enderror" 
                                           value="{{ old('pedidos.' . $loop->index . '.descuento', $pedido->descuento) }}"
                                           min="0"
                                           max="100"
                                           required>
                                    @error('pedidos.' . $loop->index . '.descuento')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <a href="{{ route('transferencias.confirmados') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
