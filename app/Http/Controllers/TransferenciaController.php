<?php

namespace App\Http\Controllers;

use App\Models\Transferencia;
use App\Models\TransferenciaConfirmada;
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

        $query = TransferenciaConfirmada::with(['transferencia.visitador', 'pedidosConfirmados.producto'])
            ->whereHas('transferencia', function($q) {
                $q->where('confirmada', true);
            });

        // Filtrar por fecha específica
        if ($request->fecha) {
            $fecha = Carbon::createFromFormat('Y-m-d', $request->fecha);
            $query->whereHas('transferencia', function($q) use ($fecha) {
                $q->whereDate('created_at', $fecha);
            });
        }

        // Filtrar por visitador
        if ($request->visitador_id) {
            $query->whereHas('transferencia', function($q) use ($request) {
                $q->where('visitador_id', $request->visitador_id);
            });
        }

        $transferencias = $query->get()->map(function($confirmacion) {
            $transferencia = $confirmacion->transferencia;
            return [
                'fecha_transferencia' => Carbon::parse($transferencia->created_at),
                'fecha_confirmacion' => Carbon::parse($confirmacion->created_at),
                'visitador' => $transferencia->visitador ? $transferencia->visitador->nombre : 'Sin Visitador',
                'transferencia_numero' => $transferencia->transferencia_numero,
                'pedidos' => $confirmacion->pedidosConfirmados->map(function($pedido) {
                    return [
                        'producto' => $pedido->producto ? $pedido->producto->nombre : 'Producto no encontrado',
                        'cantidad' => $pedido->cantidad,
                        'descuento' => $pedido->descuento
                    ];
                })
            ];
        });

        return view('transferencias.confirmados', compact('transferencias', 'visitadores'));
    }
}
