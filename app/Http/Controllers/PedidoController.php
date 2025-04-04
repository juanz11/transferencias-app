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
            $query->whereHas('transferenciaConfirmada', function($q) use ($request) {
                $q->whereBetween('created_at', [
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

        return view('pedidos.reporte', compact(
            'pedidos',
            'pedidosAgrupados',
            'resumenVisitador',
            'totalProductos',
            'tipoVista'
        ));
    }
}
