<?php

namespace App\Http\Controllers;

use App\Models\PedidoConfirmado;
use App\Models\Visitador;
use App\Models\Drogeria;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PedidoController extends Controller
{
    public function index()
    {
        $visitadores = Visitador::all();
        $drogerias = Drogeria::all();
        return view('pedidos.index', compact('visitadores', 'drogerias'));
    }

    private function getPedidosData(Request $request)
    {
        $query = PedidoConfirmado::with([
            'transferenciaConfirmada.transferencia.visitador',
            'transferenciaConfirmada.transferencia.cliente',
            'producto'
        ]);

        if ($request->fecha_inicio && $request->fecha_fin) {
            $query->whereHas('transferenciaConfirmada', function($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->fecha_inicio)
                  ->whereDate('created_at', '<=', $request->fecha_fin);
            });
        }

        if ($request->visitador_id) {
            $query->whereHas('transferenciaConfirmada.transferencia', function($q) use ($request) {
                $q->where('visitador_id', $request->visitador_id);
            });
        }

        if ($request->drogueria_id) {
            $query->whereHas('transferenciaConfirmada.transferencia.cliente', function($q) use ($request) {
                $q->where('drogueria', $request->drogueria_id);
            });
        }

        $pedidos = $query->get();
        $tipoVista = $request->tipo_vista ?? 'individual';

        // Preparar datos para vista agrupada
        $pedidosAgrupados = collect();
        if ($tipoVista === 'agrupado') {
            $pedidosAgrupados = $pedidos->groupBy('producto_id')
                ->map(function ($grupo) {
                    $primerPedido = $grupo->first();
                    return [
                        'fecha_transferencia' => $primerPedido->transferenciaConfirmada->transferencia->fecha_transferencia,
                        'fecha_confirmacion' => $primerPedido->transferenciaConfirmada->created_at,
                        'visitador' => $primerPedido->transferenciaConfirmada->transferencia->visitador->nombre,
                        'producto' => $primerPedido->producto->nombre,
                        'cantidad' => $grupo->sum('cantidad'),
                        'descuento' => $primerPedido->descuento,
                        'transferencias' => $grupo->map(function ($pedido) {
                            return $pedido->transferenciaConfirmada->transferencia->transferencia_numero;
                        })->unique()->implode(', ')
                    ];
                })->values();
        }

        // Agrupar por visitador para el resumen
        $resumenVisitador = $pedidos->groupBy(function($pedido) {
            return $pedido->transferenciaConfirmada->transferencia->visitador->id;
        })->map(function($grupoPedidos) {
            $visitador = $grupoPedidos->first()->transferenciaConfirmada->transferencia->visitador;
            $productos = $grupoPedidos->groupBy('producto_id')
                ->map(function($grupo) {
                    $primerPedido = $grupo->first();
                    return [
                        'producto' => $primerPedido->producto->nombre,
                        'cantidad' => $grupo->sum('cantidad')
                    ];
                })->values();
            
            return [
                'visitador' => $visitador->nombre,
                'productos' => $productos,
                'total_visitador' => $grupoPedidos->sum('cantidad')
            ];
        })->values();

        $totalProductos = $pedidos->sum('cantidad');

        return [
            'pedidos' => $pedidos,
            'pedidosAgrupados' => $pedidosAgrupados,
            'resumenVisitador' => $resumenVisitador,
            'totalProductos' => $totalProductos,
            'tipoVista' => $tipoVista
        ];
    }

    public function reportePedidos(Request $request)
    {
        $data = $this->getPedidosData($request);
        $drogerias = Drogeria::all();
        $data['drogerias'] = $drogerias;
        
        if ($request->formato === 'pdf') {
            $pdf = PDF::loadView('pedidos.pdf', $data);
            return $pdf->download('reporte-pedidos.pdf');
        }

        return view('pedidos.reporte', $data);
    }
}
