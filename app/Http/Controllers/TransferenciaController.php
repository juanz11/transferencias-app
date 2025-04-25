<?php

namespace App\Http\Controllers;

use App\Models\Transferencia;
use App\Models\TransferenciaConfirmada;
use App\Models\Visitador;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransferenciaController extends Controller
{
    public function index()
    {
        $visitadores = Visitador::all();
        return view('transferencias.index', compact('visitadores'));
    }

    public function reporteTransferencias(Request $request)
    {
        $query = Transferencia::with('visitador');

        if ($request->fecha_inicio && $request->fecha_fin) {
            $query->whereBetween('fecha_transferencia', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }

        if ($request->visitador_id) {
            $query->where('visitador_id', $request->visitador_id);
        }

        if ($request->has('confirmada') && $request->confirmada !== '') {
            $query->where('confirmada', $request->confirmada);
        }

        $transferencias = $query->get();

        return view('transferencias.reporte', compact('transferencias'));
    }

    public function listarConfirmados(Request $request)
    {
        $query = TransferenciaConfirmada::with(['transferencia.visitador', 'pedidosConfirmados.producto'])
            ->whereHas('transferencia', function($q) {
                $q->where('confirmada', true);
            });

        // Filtrar por fecha específica
        if ($request->fecha) {
            $fecha = Carbon::createFromFormat('Y-m-d', $request->fecha);
            $query->whereHas('transferencia', function($q) use ($fecha) {
                $q->whereDate('fecha_transferencia', $fecha);
            });
        }

        $transferencias = $query->get()
            ->map(function ($confirmacion) {
                $transferencia = $confirmacion->transferencia;
                return [
                    'fecha_transferencia' => $transferencia->fecha_transferencia,
                    'fecha_confirmacion' => $confirmacion->created_at,
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

        return view('transferencias.confirmados', compact('transferencias'));
    }
}
