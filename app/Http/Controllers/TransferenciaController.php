<?php

namespace App\Http\Controllers;

use App\Models\Transferencia;
use App\Models\TransferenciaConfirmada;
use App\Models\PedidoConfirmado;
use App\Models\Visitador;
use App\Models\Producto;
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

        $visitadores = Visitador::orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get();

        return view('transferencias.edit-confirmada', compact('transferenciaConfirmada', 'visitadores', 'productos'));
    }

    public function actualizarConfirmada(Request $request, $id)
    {
        $request->validate([
            'fecha_transferencia' => 'required|date',
            'fecha_confirmacion' => 'required|date',
            'transferencia_numero' => 'required|string',
            'visitador_id' => 'required|exists:visitadores,id',
            'productos' => 'required|array|min:1',
            'productos.*' => 'required|exists:productos,id',
            'cantidades' => 'required|array|min:1',
            'cantidades.*' => 'required|integer|min:1',
            'descuentos' => 'required|array|min:1',
            'descuentos.*' => 'required|integer|min:0|max:100',
        ]);

        $transferenciaConfirmada = TransferenciaConfirmada::with('transferencia')->findOrFail($id);
        
        // Actualizar la transferencia
        $transferenciaConfirmada->transferencia->fecha_transferencia = $request->fecha_transferencia;
        $transferenciaConfirmada->transferencia->transferencia_numero = $request->transferencia_numero;
        $transferenciaConfirmada->transferencia->visitador_id = $request->visitador_id;
        $transferenciaConfirmada->transferencia->save();

        // Actualizar la fecha de confirmación
        $transferenciaConfirmada->created_at = $request->fecha_confirmacion;
        $transferenciaConfirmada->save();

        // Eliminar todos los pedidos confirmados existentes
        $transferenciaConfirmada->pedidosConfirmados()->delete();

        // Crear los nuevos pedidos confirmados
        foreach ($request->productos as $index => $productoId) {
            PedidoConfirmado::create([
                'transferencia_confirmada_id' => $transferenciaConfirmada->id,
                'producto_id' => $productoId,
                'cantidad' => $request->cantidades[$index],
                'descuento' => $request->descuentos[$index],
            ]);
        }

        return redirect()->route('transferencias.confirmados')
            ->with('success', 'Transferencia confirmada actualizada exitosamente');
    }
}
