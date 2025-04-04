<?php

namespace App\Http\Controllers;

use App\Models\PedidoConfirmado;
use App\Models\Visitador;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index()
    {
        $visitadores = Visitador::all();
        return view('pedidos.index', compact('visitadores'));
    }

    public function reportePedidos(Request $request)
    {
        $query = PedidoConfirmado::with(['transferenciaConfirmada.transferencia.visitador', 'producto']);

        if ($request->fecha_inicio && $request->fecha_fin) {
            $query->whereHas('transferenciaConfirmada.transferencia', function($q) use ($request) {
                $q->whereBetween('fecha_transferencia', [
                    $request->fecha_inicio,
                    $request->fecha_fin
                ]);
            });
        }

        if ($request->visitador_id) {
            $query->whereHas('transferenciaConfirmada.transferencia', function($q) use ($request) {
                $q->where('visitador_id', $request->visitador_id);
            });
        }

        $pedidos = $query->get();
        $totalProductos = $pedidos->sum('cantidad');

        return view('pedidos.reporte', compact('pedidos', 'totalProductos'));
    }
}
