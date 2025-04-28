<?php

namespace App\Http\Controllers;

use App\Models\Transferencia;
use App\Models\TransferenciaConfirmada;
use App\Models\PedidoConfirmado;
use App\Models\Visitador;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransferenciaController extends Controller
{
    public function index()
    {
        $query = Transferencia::with('visitador')
            ->where('confirmada', false);

        // Filtrar por fecha específica
        if (request()->fecha) {
            $fecha = Carbon::createFromFormat('Y-m-d', request()->fecha);
            $query->whereDate('created_at', $fecha);
        }

        $transferencias = $query->get();

        return view('transferencias.reporte', compact('transferencias'));
    }

    public function listarConfirmados(Request $request)
    {
        // Obtener lista de visitadores para el selector
        $visitadores = Visitador::orderBy('nombre')->get();

        $query = PedidoConfirmado::with([
            'transferenciaConfirmada.transferencia.visitador',
            'producto'
        ]);

        // Filtrar por fecha específica (fecha de confirmación)
        if ($request->fecha) {
            $fecha = Carbon::createFromFormat('Y-m-d', $request->fecha);
            $query->whereHas('transferenciaConfirmada', function($q) use ($fecha) {
                $q->whereDate('created_at', $fecha);
            });
        }

        // Filtrar por visitador
        if ($request->visitador_id) {
            $query->whereHas('transferenciaConfirmada.transferencia', function($q) use ($request) {
                $q->where('visitador_id', $request->visitador_id);
            });
        }

        // Agrupar pedidos por transferencia
        $pedidos = $query->get();
        
        // Debug: Verificar si hay pedidos
        \Log::info('Número de pedidos encontrados: ' . $pedidos->count());
        
        $transferencias = collect();

        foreach ($pedidos->groupBy('transferenciaConfirmada.id') as $confirmacionId => $pedidosGroup) {
            $primerPedido = $pedidosGroup->first();
            
            // Debug: Verificar el primer pedido y sus relaciones
            \Log::info('Confirmación ID: ' . $confirmacionId);
            \Log::info('Primer pedido existe: ' . ($primerPedido ? 'Sí' : 'No'));
            \Log::info('TransferenciaConfirmada existe: ' . ($primerPedido->transferenciaConfirmada ? 'Sí' : 'No'));
            
            if (!$primerPedido || !$primerPedido->transferenciaConfirmada || !$primerPedido->transferenciaConfirmada->transferencia) {
                \Log::error('Datos faltantes para confirmación ID: ' . $confirmacionId);
                continue;
            }
            
            $transferencia = $primerPedido->transferenciaConfirmada->transferencia;
            
            $transferencias->push([
                'id' => $primerPedido->transferenciaConfirmada->id,
                'fecha_transferencia' => $transferencia->fecha_transferencia,
                'fecha_confirmacion' => $primerPedido->transferenciaConfirmada->created_at,
                'visitador' => $transferencia->visitador ? $transferencia->visitador->nombre : 'Sin Visitador',
                'transferencia_numero' => $transferencia->transferencia_numero,
                'pedidos' => $pedidosGroup->map(function($pedido) {
                    return [
                        'producto' => $pedido->producto ? $pedido->producto->nombre : 'Producto no encontrado',
                        'cantidad' => $pedido->cantidad,
                        'descuento' => $pedido->descuento
                    ];
                })
            ]);
        }

        return view('transferencias.confirmados', compact('transferencias', 'visitadores'));
    }

    public function editarConfirmada($id)
    {
        $transferenciaConfirmada = TransferenciaConfirmada::with([
            'transferencia.visitador',
            'pedidosConfirmados.producto'
        ])->findOrFail($id);

        return view('transferencias.edit-confirmada', compact('transferenciaConfirmada'));
    }

    public function actualizarConfirmada(Request $request, $id)
    {
        $transferenciaConfirmada = TransferenciaConfirmada::findOrFail($id);
        
        // Validar los datos
        $request->validate([
            'pedidos.*.cantidad' => 'required|integer|min:1',
            'pedidos.*.descuento' => 'required|integer|min:0|max:100',
        ]);

        // Actualizar los pedidos
        foreach ($request->pedidos as $pedidoData) {
            $pedido = PedidoConfirmado::find($pedidoData['id']);
            if ($pedido && $pedido->transferencia_confirmada_id == $id) {
                $pedido->update([
                    'cantidad' => $pedidoData['cantidad'],
                    'descuento' => $pedidoData['descuento'],
                ]);
            }
        }

        return redirect()->route('transferencias.confirmados')
            ->with('success', 'Transferencia actualizada correctamente');
    }
}
