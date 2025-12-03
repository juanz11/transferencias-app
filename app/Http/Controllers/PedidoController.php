<?php

namespace App\Http\Controllers;

use App\Models\PedidoConfirmado;
use App\Models\Visitador;
use App\Models\Drogeria;
use App\Models\Pedido;
use App\Models\Transferencia;
use App\Models\TransferenciaConfirmada;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\ReporteVisitador;
use App\Mail\TransferenciaConfirmada as TransferenciaConfirmadaMail;
use Illuminate\Support\Facades\Mail;

class PedidoController extends Controller
{
    public function index()
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $visitadores = Visitador::all();
        $drogerias = Drogeria::all();
        return view('pedidos.index', compact('visitadores', 'drogerias'));
    }

    public function pendientes()
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $transferencias = Transferencia::with(['visitador', 'cliente', 'pedidos.producto'])
            ->whereHas('pedidos', function($q) {
                $q->where('estado', 'pendiente');
            })
            ->orderByDesc('fecha_transferencia')
            ->get();

        return view('admin.pedidos.pendientes', compact('transferencias'));
    }

    public function showPendiente(Transferencia $transferencia)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $transferencia->load(['visitador', 'cliente.drogueria', 'pedidos.producto']);

        return view('admin.pedidos.show', compact('transferencia'));
    }

    public function cambiarEstado(Request $request, Transferencia $transferencia)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $request->validate([
            'estado' => 'required|in:aprobado,rechazado',
        ]);

        $estado = $request->input('estado');

        \DB::beginTransaction();
        try {
            $pedidosPendientes = $transferencia->pedidos()->where('estado', 'pendiente')->get();

            if ($pedidosPendientes->isEmpty()) {
                \DB::rollBack();
                return redirect()->route('admin.pedidos.pendientes')
                    ->with('error', 'No hay pedidos pendientes para esta transferencia.');
            }

            if ($estado === 'rechazado') {
                $transferencia->pedidos()->where('estado', 'pendiente')->update(['estado' => 'rechazado']);
                \DB::commit();
                return redirect()->route('admin.pedidos.pendientes')
                    ->with('success', 'Pedidos marcados como rechazados.');
            }

            // Aprobado: crear transferencia_confirmada y pedidos_confirmados
            $transferenciaConfirmada = TransferenciaConfirmada::create([
                'user_id' => auth()->id(),
                'transferencia_id' => $transferencia->id,
            ]);

            $calculos = [];
            foreach ($pedidosPendientes as $pedido) {
                $pedidoConfirmado = PedidoConfirmado::create([
                    'transferencia_confirmada_id' => $transferenciaConfirmada->id,
                    'producto_id' => $pedido->producto_id,
                    'cantidad' => $pedido->cantidad,
                    'descuento' => $pedido->descuento,
                ]);

                $producto = $pedidoConfirmado->producto;
                $calculos[] = (object) [
                    'productos' => $producto,
                    'cantidad' => $pedidoConfirmado->cantidad,
                    'comision' => $producto->comision,
                    'total' => $pedidoConfirmado->cantidad * $producto->comision,
                ];
            }

            $transferencia->confirmada = true;
            $transferencia->save();

            $transferencia->pedidos()->where('estado', 'pendiente')->update(['estado' => 'aprobado']);

            // Enviar correo al visitador y a todos los usuarios (no bloquear si falla)
            $visitador = $transferencia->visitador;
            $drogueria = Drogeria::findOrFail($transferencia->cliente->drogueria);

            $recipients = collect();
            if ($visitador && $visitador->email) {
                $recipients->push($visitador->email);
            }

            $userEmails = User::whereNotNull('email')->pluck('email');
            $recipients = $recipients->merge($userEmails)->unique();

            try {
                if ($recipients->isNotEmpty()) {
                    Mail::to($recipients)->send(new TransferenciaConfirmadaMail($transferenciaConfirmada, $calculos, $drogueria));
                }
            } catch (\Exception $mailError) {
                \Log::error('Error al enviar correo de transferencia confirmada: ' . $mailError->getMessage());
            }

            \DB::commit();

            return redirect()->route('admin.pedidos.pendientes')
                ->with('success', 'Pedidos aprobados, confirmados y notificados correctamente.');

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('admin.pedidos.pendientes')
                ->with('error', 'Error al cambiar el estado de los pedidos: ' . $e->getMessage());
        }
    }

    public function reporteVisitador(Request $request)
    {
        if (!auth()->check() || auth()->user()->rol !== 'visitador') {
            return redirect()->route('visitador.home');
        }

        $userEmail = auth()->user()->email;
        $visitador = Visitador::where('email', $userEmail)->firstOrFail();

        $query = Pedido::with(['transferencia.cliente.drogueria', 'producto'])
            ->where('estado', 'pendiente')
            ->whereHas('transferencia', function($q) use ($visitador) {
                $q->where('visitador_id', $visitador->id);
            });

        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereHas('transferencia', function($q) use ($request) {
                $q->whereDate('fecha_transferencia', '>=', $request->fecha_inicio)
                  ->whereDate('fecha_transferencia', '<=', $request->fecha_fin);
            });
        }

        $pedidos = $query->get();

        return view('visitor.pedidos.reporte', [
            'pedidos' => $pedidos,
            'visitador' => $visitador,
        ]);
    }

    private function getPedidosData(Request $request)
    {
        $query = PedidoConfirmado::with([
            'transferenciaConfirmada.transferencia.visitador',
            'transferenciaConfirmada.transferencia.cliente.drogueria',
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
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $tipoVista = $request->input('tipo_vista', 'individual');
        $visitadorId = $request->input('visitador_id') ?: $request->input('visitador');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        // Obtener pedidos
        $query = PedidoConfirmado::query()
            ->join('transferencias_confirmadas', 'pedidos_confirmados.transferencia_confirmada_id', '=', 'transferencias_confirmadas.id')
            ->join('transferencias', 'transferencias_confirmadas.transferencia_id', '=', 'transferencias.id')
            ->join('productos', 'pedidos_confirmados.producto_id', '=', 'productos.id')
            ->join('clientes', 'transferencias.cliente_id', '=', 'clientes.id')
            ->orderBy('productos.id');

        if ($visitadorId) {
            $query->where('transferencias.visitador_id', $visitadorId);
        }

        if ($fechaInicio && $fechaFin) {
            $query->whereDate('transferencias_confirmadas.created_at', '>=', $fechaInicio)
                  ->whereDate('transferencias_confirmadas.created_at', '<=', $fechaFin);
        }

        if ($request->input('drogueria_id')) {
            $query->where('clientes.drogueria', $request->input('drogueria_id'));
        }

        if ($request->input('descuento') !== null && $request->input('descuento') !== '') {
            $query->where('pedidos_confirmados.descuento', $request->input('descuento'));
        }

        $pedidos = $query->get();

        // Ordenar la colecci贸n por producto_id para asegurar el orden
        $pedidos = $pedidos->sortBy(function($pedido) {
            return $pedido->producto_id;
        })->values();

        // Preparar datos para la vista
        $data = [
            'tipoVista' => $tipoVista,
            'pedidos' => $pedidos
        ];

        // Si es vista agrupada, agrupar los pedidos
        if ($tipoVista === 'agrupado') {
            $pedidosAgrupados = [];
            foreach ($pedidos->sortBy('producto_id') as $pedido) {
                $key = $pedido->producto->nombre . '-' . 
                       $pedido->transferenciaConfirmada->transferencia->visitador->nombre . '-' .
                       Drogeria::findOrFail($pedido->transferenciaConfirmada->transferencia->cliente->drogueria)->nombre . '-' .
                       $pedido->transferenciaConfirmada->transferencia->fecha_transferencia->format('Y-m-d');

                if (!isset($pedidosAgrupados[$key])) {
                    $pedidosAgrupados[$key] = [
                        'fecha_transferencia' => $pedido->transferenciaConfirmada->transferencia->fecha_transferencia,
                        'fecha_confirmacion' => $pedido->transferenciaConfirmada->created_at,
                        'visitador' => $pedido->transferenciaConfirmada->transferencia->visitador->nombre,
                        'drogueria' => Drogeria::findOrFail($pedido->transferenciaConfirmada->transferencia->cliente->drogueria)->nombre,
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
        foreach ($pedidos->sortBy('producto_id') as $pedido) {
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
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        try {
            $visitadorId = $request->visitador;
            $fechaInicio = $request->fecha_inicio;
            $fechaFin = $request->fecha_fin;
            
            if (!$visitadorId || !$fechaInicio || !$fechaFin) {
                return response()->json([
                    'message' => 'Error: Faltan par谩metros necesarios (visitador, fecha_inicio, fecha_fin)',
                    'data' => $request->all()
                ], 400);
            }

            // Obtener los datos del reporte
            $query = PedidoConfirmado::query()
                ->join('transferencias_confirmadas', 'pedidos_confirmados.transferencia_confirmada_id', '=', 'transferencias_confirmadas.id')
                ->join('transferencias', 'transferencias_confirmadas.transferencia_id', '=', 'transferencias.id')
                ->join('productos', 'pedidos_confirmados.producto_id', '=', 'productos.id')
                ->whereDate('transferencias_confirmadas.created_at', '>=', $fechaInicio)
                ->whereDate('transferencias_confirmadas.created_at', '<=', $fechaFin);
            $productosAgrupados = [];
            $total = 0;

            $resultados = $query->get();
            
            if ($resultados->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron pedidos para el per铆odo y visitador seleccionados'
                ], 404);
            }

            foreach ($resultados as $pedido) {
                $nombreProducto = $pedido->producto->nombre;
                $comision = $pedido->producto->comision;
                
                if (!isset($productosAgrupados[$nombreProducto])) {
                    $productosAgrupados[$nombreProducto] = [
                        'nombre' => $nombreProducto,
                        'cantidad' => 0,
                        'comision' => $comision,
                        'subtotal' => 0
                    ];
                }
                
                $productosAgrupados[$nombreProducto]['cantidad'] += $pedido->cantidad;
                $productosAgrupados[$nombreProducto]['subtotal'] += $pedido->cantidad * $comision;
            }

            $productos = array_values($productosAgrupados);
            $total = array_sum(array_column($productos, 'subtotal'));

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
                    'message' => 'Error: El visitador no tiene una direcci贸n de correo configurada'
                ], 400);
            }

            // Preparar lista de destinatarios
            $recipients = [$visitadorModel->email];
            
            // Obtener emails de todos los usuarios
            $allUsers = \App\Models\User::whereNotNull('email')->pluck('email')->toArray();
            $recipients = array_merge($recipients, $allUsers);
            $recipients = array_unique($recipients); // Eliminar duplicados

            // Enviar el email a todos los destinatarios
            Mail::to($recipients)
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
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

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
