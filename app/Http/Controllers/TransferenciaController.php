<?php

namespace App\Http\Controllers;

use App\Models\Transferencia;
use App\Models\TransferenciaConfirmada;
use App\Models\Visitador;
use Illuminate\Http\Request;

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
}
