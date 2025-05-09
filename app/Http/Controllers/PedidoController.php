<?php

namespace App\Http\Controllers;

use App\Models\PedidoConfirmado;
use App\Models\Visitador;
use App\Models\Drogeria;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\ReporteVisitador;
use Illuminate\Support\Facades\Mail;

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

        if ($request->descuento !== null && $request->descuento !== '') {
            $query->where('descuento', $request->descuento);
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

    public function reporte(Request $request)
    {
        $tipoVista = $request->input('tipo_vista', 'individual');
        $visitadorId = $request->input('visitador');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        // Obtener pedidos
        $query = PedidoConfirmado::query()
            ->join('transferencias_confirmadas', 'pedidos_confirmados.transferencia_confirmada_id', '=', 'transferencias_confirmadas.id')
            ->join('transferencias', 'transferencias_confirmadas.transferencia_id', '=', 'transferencias.id')
            ->join('productos', 'pedidos_confirmados.producto_id', '=', 'productos.id');

        if ($visitadorId) {
            $query->where('transferencias.visitador_id', $visitadorId);
        }

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('transferencias.fecha_transferencia', [$fechaInicio, $fechaFin]);
        }

        if ($request->input('descuento') !== null && $request->input('descuento') !== '') {
            $query->where('pedidos_confirmados.descuento', $request->input('descuento'));
        }

        $pedidos = $query->get();

        // Preparar datos para la vista
        $data = [
            'tipoVista' => $tipoVista,
            'pedidos' => $pedidos
        ];

        // Si es vista agrupada, agrupar los pedidos
        if ($tipoVista === 'agrupado') {
            $pedidosAgrupados = [];
            foreach ($pedidos as $pedido) {
                $key = $pedido->producto->nombre . '-' . 
                       $pedido->transferenciaConfirmada->transferencia->visitador->nombre . '-' .
                       $pedido->transferenciaConfirmada->transferencia->fecha_transferencia->format('Y-m-d');

                if (!isset($pedidosAgrupados[$key])) {
                    $pedidosAgrupados[$key] = [
                        'fecha_transferencia' => $pedido->transferenciaConfirmada->transferencia->fecha_transferencia,
                        'fecha_confirmacion' => $pedido->transferenciaConfirmada->created_at,
                        'visitador' => $pedido->transferenciaConfirmada->transferencia->visitador->nombre,
                        'producto' => $pedido->producto->nombre,
                        'cantidad' => 0,
                        'descuento' => $pedido->descuento,
                        'transferencias' => ''
                    ];
                }

                $pedidosAgrupados[$key]['cantidad'] += $pedido->cantidad;
                $transferencia = $pedido->transferenciaConfirmada->transferencia->transferencia_numero;
                if (!str_contains($pedidosAgrupados[$key]['transferencias'], $transferencia)) {
                    $pedidosAgrupados[$key]['transferencias'] .= ($pedidosAgrupados[$key]['transferencias'] ? ', ' : '') . $transferencia;
                }
            }

            $data['pedidosAgrupados'] = array_values($pedidosAgrupados);
        }

        // Preparar resumen por visitador
        $resumenVisitador = [];
        foreach ($pedidos as $pedido) {
            $visitadorNombre = $pedido->transferenciaConfirmada->transferencia->visitador->nombre;
            $productoNombre = $pedido->producto->nombre;

            if (!isset($resumenVisitador[$visitadorNombre])) {
                $resumenVisitador[$visitadorNombre] = [
                    'visitador' => $visitadorNombre,
                    'productos' => [],
                    'total_visitador' => 0
                ];
            }

            if (!isset($resumenVisitador[$visitadorNombre]['productos'][$productoNombre])) {
                $resumenVisitador[$visitadorNombre]['productos'][$productoNombre] = [
                    'producto' => $productoNombre,
                    'cantidad' => 0
                ];
            }

            $resumenVisitador[$visitadorNombre]['productos'][$productoNombre]['cantidad'] += $pedido->cantidad;
            $resumenVisitador[$visitadorNombre]['total_visitador'] += $pedido->cantidad;
        }

        // Convertir productos de array asociativo a array indexado
        foreach ($resumenVisitador as &$visitador) {
            $visitador['productos'] = array_values($visitador['productos']);
        }

        $data['resumenVisitador'] = array_values($resumenVisitador);
        $data['totalProductos'] = array_sum(array_column($data['resumenVisitador'], 'total_visitador'));

        // Si se solicita formato PDF, generar y devolver el PDF
        if ($request->input('formato') === 'pdf') {
            $pdf = PDF::loadView('pedidos.pdf', $data);
            return $pdf->download('reporte-pedidos.pdf');
        }

        return view('pedidos.reporte', $data);
    }

    public function enviarReporteEmail(Request $request)
    {
        try {
            $visitadorId = $request->visitador;
            $fechaInicio = $request->fecha_inicio;
            $fechaFin = $request->fecha_fin;
            
            if (!$visitadorId || !$fechaInicio || !$fechaFin) {
                return response()->json([
                    'message' => 'Error: Faltan parámetros necesarios (visitador, fecha_inicio, fecha_fin)',
                    'data' => $request->all()
                ], 400);
            }

            // Obtener los datos del reporte
            $query = PedidoConfirmado::query()
                ->join('transferencias_confirmadas', 'pedidos_confirmados.transferencia_confirmada_id', '=', 'transferencias_confirmadas.id')
                ->join('transferencias', 'transferencias_confirmadas.transferencia_id', '=', 'transferencias.id')
                ->join('productos', 'pedidos_confirmados.producto_id', '=', 'productos.id')
                ->where('transferencias.visitador_id', $visitadorId)
                ->whereBetween('transferencias.fecha_transferencia', [$fechaInicio, $fechaFin]);

            $productos = [];
            $total = 0;

            $resultados = $query->get();
            
            if ($resultados->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron pedidos para el período y visitador seleccionados'
                ], 404);
            }

            foreach ($resultados as $pedido) {
                $subtotal = $pedido->cantidad * $pedido->producto->comision;
                $productos[] = [
                    'nombre' => $pedido->producto->nombre,
                    'cantidad' => $pedido->cantidad,
                    'comision' => $pedido->producto->comision,
                    'subtotal' => $subtotal
                ];
                $total += $subtotal;
            }

            $visitadorModel = Visitador::find($visitadorId);
            if (!$visitadorModel) {
                return response()->json([
                    'message' => 'Error: Visitador no encontrado',
                    'visitador_id' => $visitadorId
                ], 404);
            }

            $visitadorNombre = $visitadorModel->nombre;
            $fechaInicioFormat = date('d-m-Y', strtotime($fechaInicio));
            $fechaFinFormat = date('d-m-Y', strtotime($fechaFin));

            // Verificar que el visitador tenga email
            if (!$visitadorModel->email) {
                return response()->json([
                    'message' => 'Error: El visitador no tiene una dirección de correo configurada'
                ], 400);
            }

            // Enviar el email al visitador
            Mail::to($visitadorModel->email)
                ->send(new ReporteVisitador(
                    $visitadorNombre,
                    $fechaInicioFormat,
                    $fechaFinFormat,
                    $productos,
                    $total
                ));

            return response()->json(['message' => 'Reporte enviado por email exitosamente']);

        } catch (\Exception $e) {
            \Log::error('Error al enviar email: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error al enviar el reporte por email: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'visitador_id' => 'required|exists:visitadors,id',
            'codigo_cliente' => 'required|exists:clientes,codigo_cliente',
            'fecha_transferencia' => 'required|date',
            'fecha_correo' => 'required|date',
            'transferencia_numero' => 'required|string',
            'productos' => 'required|array|min:1',
            'productos.*.codigo' => 'required|exists:productos,codigo',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $pedido = Pedido::create([
                'visitador_id' => $request->visitador_id,
                'codigo_cliente' => $request->codigo_cliente,
                'fecha_transferencia' => $request->fecha_transferencia,
                'fecha_correo' => $request->fecha_correo,
                'transferencia_numero' => $request->transferencia_numero,
            ]);

            foreach ($request->productos as $producto) {
                $pedido->productos()->create([
                    'codigo_producto' => $producto['codigo'],
                    'cantidad' => $producto['cantidad'],
                ]);
            }

            $visitador = Visitador::find($request->visitador_id);
            Mail::to($visitador->email)->send(new ReporteVisitador($pedido));

            DB::commit();
            return redirect()->route('pedidos.index')->with('success', 'Pedido creado exitosamente');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al crear el pedido: ' . $e->getMessage());
        }
    }
}
