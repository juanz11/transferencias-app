<?php

namespace App\Http\Controllers;

use App\Models\PedidoConfirmado;
use App\Models\Visitador;
use App\Models\Drogeria;
use App\Models\Pedido;
use App\Models\Transferencia;
use App\Models\TransferenciaConfirmada;
use App\Models\Cliente;
use App\Models\Producto;
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
            ->orderBy('transferencia_numero')
            ->get();

        $drogueriaIds = $transferencias
            ->pluck('cliente.drogueria')
            ->filter()
            ->unique()
            ->values();

        $drogueriasPorId = Drogeria::whereIn('id', $drogueriaIds)->get()->keyBy('id');

        foreach ($transferencias as $transferencia) {
            $drogueriaNombre = 'Sin Droguería';
            if ($transferencia->cliente && $transferencia->cliente->drogueria) {
                $drogueriaNombre = $drogueriasPorId[$transferencia->cliente->drogueria]->nombre ?? 'Sin Droguería';
            }
            $transferencia->setAttribute('drogueria_nombre', $drogueriaNombre);
        }

        return view('admin.pedidos.pendientes', compact('transferencias'));
    }

    public function showPendiente(Transferencia $transferencia)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $transferencia->load(['visitador', 'cliente', 'pedidos.producto']);

        $drogueria = null;
        if ($transferencia->cliente && $transferencia->cliente->drogueria) {
            $drogueria = Drogeria::find($transferencia->cliente->drogueria);
        }

        return view('admin.pedidos.show', compact('transferencia', 'drogueria'));
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

            // Enviar correo al visitador y a los usuarios con rol admin (no bloquear si falla)
            $visitador = $transferencia->visitador;
            $drogueria = Drogeria::findOrFail($transferencia->cliente->drogueria);

            $recipients = collect();
            if ($visitador && $visitador->email) {
                $recipients->push($visitador->email);
            }

            // Agregar solo emails de usuarios administradores
            $adminEmails = User::where('rol', 'admin')
                ->whereNotNull('email')
                ->pluck('email');
            $recipients = $recipients->merge($adminEmails)->unique();

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

    public function editPendiente(Transferencia $transferencia)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $transferencia->load(['visitador', 'cliente.drogueria']);
        $visitadores = Visitador::orderBy('nombre')->get();
        $droguerias = Drogeria::orderBy('nombre')->get();
        $clientes = Cliente::orderBy('nombre_cliente')->get();
        $productos = Producto::orderBy('nombre')->get();

        return view('admin.pedidos.edit', compact('transferencia', 'visitadores', 'droguerias', 'clientes', 'productos'));
    }

    public function updatePendiente(Request $request, Transferencia $transferencia)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $request->validate([
            'visitador_id' => 'required|exists:visitadores,id',
            'transferencia_numero' => 'required|string',
            'codigo_cliente' => 'required|exists:clientes,codigo_cliente',
            'pedido_ids' => 'nullable|array',
            'pedido_ids.*' => 'required_with:pedido_ids|exists:pedidos,id',
            'producto_ids' => 'nullable|array',
            'producto_ids.*' => 'required_with:pedido_ids|exists:productos,id',
            'cantidades' => 'nullable|array',
            'cantidades.*' => 'required_with:pedido_ids|integer|min:1',
            'descuentos' => 'nullable|array',
            'descuentos.*' => 'nullable|integer|min:0|max:100',
            'nuevos_producto_ids' => 'nullable|array',
            'nuevos_producto_ids.*' => 'required_with:nuevos_producto_ids|exists:productos,id',
            'nuevos_cantidades' => 'nullable|array',
            'nuevos_cantidades.*' => 'required_with:nuevos_producto_ids|integer|min:1',
            'nuevos_descuentos' => 'nullable|array',
            'nuevos_descuentos.*' => 'nullable|integer|min:0|max:100',
        ]);

        \DB::beginTransaction();
        try {
            // Buscar el cliente por su código (igual que en crear pedido)
            $cliente = Cliente::where('codigo_cliente', $request->codigo_cliente)->firstOrFail();

            $pedidoIds = $request->input('pedido_ids', []);
            $nuevosProductoIds = $request->input('nuevos_producto_ids', []);
            if (count($pedidoIds) + count($nuevosProductoIds) === 0) {
                \DB::rollBack();
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Debe quedar al menos un producto en la transferencia.');
            }

            // Actualizar datos de la transferencia
            $transferencia->cliente_id = $cliente->id;
            $transferencia->visitador_id = $request->visitador_id;
            $transferencia->transferencia_numero = $request->transferencia_numero;
            $transferencia->save();

            // Actualizar pedidos (productos, cantidades, descuentos)
            foreach ($pedidoIds as $index => $pedidoId) {
                $pedido = Pedido::where('transferencia_id', $transferencia->id)
                    ->where('id', $pedidoId)
                    ->firstOrFail();

                $pedido->producto_id = $request->producto_ids[$index] ?? $pedido->producto_id;
                $pedido->cantidad = $request->cantidades[$index] ?? $pedido->cantidad;
                $pedido->descuento = $request->descuentos[$index] ?? 0;
                $pedido->save();
            }

            // Eliminar de la BD los pedidos pendientes que fueron quitados en el formulario
            $pedidoIdsInt = collect($pedidoIds)->map(fn($id) => (int) $id)->filter()->values();
            $pendientesQuery = $transferencia->pedidos()->where('estado', 'pendiente');
            if ($pedidoIdsInt->isEmpty()) {
                $pendientesQuery->delete();
            } else {
                $pendientesQuery->whereNotIn('id', $pedidoIdsInt->all())->delete();
            }

            // Crear nuevos pedidos (si se agregaron productos en la edición)
            $nuevasCantidades = $request->input('nuevos_cantidades', []);
            $nuevosDescuentos = $request->input('nuevos_descuentos', []);

            foreach ($nuevosProductoIds as $index => $productoId) {
                $cantidad = $nuevasCantidades[$index] ?? null;
                if ($cantidad === null) {
                    continue;
                }

                Pedido::create([
                    'transferencia_id' => $transferencia->id,
                    'producto_id' => $productoId,
                    'cantidad' => $cantidad,
                    'descuento' => $nuevosDescuentos[$index] ?? 0,
                    'estado' => 'pendiente',
                ]);
            }

            \DB::commit();

            return redirect()->route('admin.pedidos.show', $transferencia)
                ->with('success', 'Transferencia y pedidos actualizados correctamente.');

        } catch (\Exception $e) {
            \DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la transferencia: ' . $e->getMessage());
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
            })
            ->orderByDesc('created_at');

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
            'pedidos' => $pedidos,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
        ];

        // Si es vista agrupada, agrupar los pedidos
        if ($tipoVista === 'agrupado') {
            $pedidosAgrupados = [];
            foreach ($pedidos->sortBy('producto_id') as $pedido) {
                $transferencia = optional(optional($pedido->transferenciaConfirmada)->transferencia);
                $cliente = optional($transferencia)->cliente;
                $clienteNombre = optional($cliente)->nombre_cliente ?? 'Sin Cliente';

                $drogueriaNombre = '';
                if ($cliente && $cliente->drogueria) {
                    $drogueriaNombre = Drogeria::find($cliente->drogueria)->nombre ?? '';
                }

                $visitadorNombre = optional($transferencia->visitador)->nombre ?? '';
                $fechaTransferencia = $transferencia->fecha_transferencia;
                $key = $pedido->producto->nombre . '-' . 
                       $visitadorNombre . '-' .
                       $clienteNombre . '-' .
                       $drogueriaNombre . '-' .
                       (optional($fechaTransferencia)->format('Y-m-d') ?? '');

                if (!isset($pedidosAgrupados[$key])) {
                    $pedidosAgrupados[$key] = [
                        'fecha_transferencia' => $fechaTransferencia,
                        'fecha_confirmacion' => optional($pedido->transferenciaConfirmada)->created_at,
                        'visitador' => $visitadorNombre,
                        'farmacia' => $clienteNombre,
                        'drogueria' => $drogueriaNombre,
                        'producto' => $pedido->producto->nombre,
                        'cantidad' => 0,
                        'descuento' => $pedido->descuento,
                        'transferencias' => ''
                    ];
                }

                $pedidosAgrupados[$key]['cantidad'] += $pedido->cantidad;
                $transferenciaNumero = $transferencia->transferencia_numero;
                if ($transferenciaNumero && !str_contains($pedidosAgrupados[$key]['transferencias'], $transferenciaNumero)) {
                    $pedidosAgrupados[$key]['transferencias'] .= ($pedidosAgrupados[$key]['transferencias'] ? ', ' : '') . $transferenciaNumero;
                }
            }

            $data['pedidosAgrupados'] = array_values($pedidosAgrupados);
        }

        // Preparar resumen por visitador
        $resumenVisitador = [];
        foreach ($pedidos->sortBy('producto_id') as $pedido) {
            $transferencia = optional(optional($pedido->transferenciaConfirmada)->transferencia);
            $visitadorNombre = optional($transferencia->visitador)->nombre ?? 'Sin Visitador';
            $productoNombre = optional($pedido->producto)->nombre ?? 'Sin Producto';

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

        if ($request->input('formato') === 'excel') {
            $rows = [];
            $headers = [
                'Fecha Transferencia',
                'Fecha Confirmación',
                'Visitador',
                'Farmacia',
                'Droguería',
                'Producto',
                'Cantidad',
                'Descuento',
                'N° Transferencia',
                'Ganancia',
            ];

            $rows[] = $headers;

            if ($tipoVista === 'agrupado') {
                $productosNombres = collect($data['pedidosAgrupados'] ?? [])->pluck('producto')->unique()->values();
                $comisionPorProducto = Producto::whereIn('nombre', $productosNombres)
                    ->get()
                    ->keyBy('nombre')
                    ->map(function ($p) {
                        return $p->comision;
                    });

                foreach (($data['pedidosAgrupados'] ?? []) as $pedido) {
                    $comision = $comisionPorProducto[$pedido['producto']] ?? 0;
                    $ganancia = ((int) $pedido['cantidad']) * ((float) $comision);
                    $rows[] = [
                        $pedido['fecha_transferencia'] ? $pedido['fecha_transferencia']->format('d/m/Y') : '',
                        $pedido['fecha_confirmacion'] ? $pedido['fecha_confirmacion']->format('d/m/Y') : '',
                        $pedido['visitador'] ?? '',
                        $pedido['farmacia'] ?? '',
                        $pedido['drogueria'] ?? '',
                        $pedido['producto'] ?? '',
                        $pedido['cantidad'] ?? 0,
                        ($pedido['descuento'] ?? 0) . '%',
                        $pedido['transferencias'] ?? '',
                        number_format($ganancia, 2, '.', ''),
                    ];
                }
            } else {
                $pedidosConRelaciones = PedidoConfirmado::with([
                    'transferenciaConfirmada.transferencia.visitador',
                    'transferenciaConfirmada.transferencia.cliente.drogueria',
                    'producto'
                ])->whereIn('id', $pedidos->pluck('id')->all())->get();

                foreach ($pedidosConRelaciones as $pedido) {
                    $transferencia = optional(optional($pedido->transferenciaConfirmada)->transferencia);
                    $cliente = optional($transferencia)->cliente;
                    $clienteNombre = optional($cliente)->nombre_cliente ?? 'Sin Cliente';
                    $drogueriaNombre = '';
                    if ($cliente && $cliente->drogueria) {
                        $drogueriaNombre = Drogeria::find($cliente->drogueria)->nombre ?? '';
                    }
                    $ganancia = ((int) $pedido->cantidad) * ((float) ($pedido->producto->comision ?? 0));
                    $rows[] = [
                        $transferencia->fecha_transferencia ? $transferencia->fecha_transferencia->format('d/m/Y') : '',
                        optional($pedido->transferenciaConfirmada)->created_at ? optional($pedido->transferenciaConfirmada)->created_at->format('d/m/Y') : '',
                        optional($transferencia->visitador)->nombre ?? '',
                        $clienteNombre,
                        $drogueriaNombre,
                        $pedido->producto->nombre ?? '',
                        $pedido->cantidad ?? 0,
                        ($pedido->descuento ?? 0) . '%',
                        $transferencia->transferencia_numero ?? '',
                        number_format($ganancia, 2, '.', ''),
                    ];
                }
            }

            $escapeHtml = function ($value) {
                return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            };

            $html = '<!doctype html><html><head><meta charset="utf-8">';
            $html .= '<style>';
            $html .= 'table{border-collapse:collapse;font-family:Arial, sans-serif;font-size:12px;}';
            $html .= 'th,td{border:1px solid #999;padding:6px;vertical-align:top;}';
            $html .= 'th{background:#f2f2f2;font-weight:bold;text-align:left;}';
            $html .= '.num{text-align:right;}';
            $html .= '</style></head><body>';
            $html .= '<table><thead><tr>';
            foreach ($headers as $h) {
                $html .= '<th>' . $escapeHtml($h) . '</th>';
            }
            $html .= '</tr></thead><tbody>';

            foreach (array_slice($rows, 1) as $row) {
                $html .= '<tr>';
                foreach ($row as $index => $cell) {
                    $isNumericCol = in_array($index, [6, 9], true);
                    $class = $isNumericCol ? ' class="num"' : '';
                    $html .= '<td' . $class . '>' . $escapeHtml($cell) . '</td>';
                }
                $html .= '</tr>';
            }

            $html .= '</tbody></table></body></html>';

            $filename = 'reporte-pedidos-' . date('Y-m-d-His') . '.xls';
            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // Si se solicita formato PDF, generar y devolver el PDF
        if ($request->input('formato') === 'resumen_pdf') {
            $pdf = PDF::loadView('pedidos.pdf_resumen', $data);
            return $pdf->download('reporte-pedidos-resumen-visitador.pdf');
        } elseif ($request->input('formato') === 'pdf') {
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

            // Obtener los datos del reporte solo para el visitador seleccionado
            $query = PedidoConfirmado::query()
                ->join('transferencias_confirmadas', 'pedidos_confirmados.transferencia_confirmada_id', '=', 'transferencias_confirmadas.id')
                ->join('transferencias', 'transferencias_confirmadas.transferencia_id', '=', 'transferencias.id')
                ->join('productos', 'pedidos_confirmados.producto_id', '=', 'productos.id')
                ->where('transferencias.visitador_id', $visitadorId)
                ->whereDate('transferencias_confirmadas.created_at', '>=', $fechaInicio)
                ->whereDate('transferencias_confirmadas.created_at', '<=', $fechaFin);
            $productosAgrupados = [];
            $total = 0;

            $resultados = $query->get();

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

            // Preparar lista de destinatarios: visitador + usuarios con rol admin
            $recipients = [$visitadorModel->email];

            $adminEmails = \App\Models\User::where('rol', 'admin')
                ->whereNotNull('email')
                ->pluck('email')
                ->toArray();
            $recipients = array_merge($recipients, $adminEmails);
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

    public function destroyPendiente(Transferencia $transferencia)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        \DB::beginTransaction();
        try {
            $pedidosPendientesQuery = $transferencia->pedidos()->where('estado', 'pendiente');
            $cantidadPendientes = (int) $pedidosPendientesQuery->count();

            if ($cantidadPendientes === 0) {
                \DB::rollBack();
                return redirect()->route('admin.pedidos.pendientes')
                    ->with('error', 'No hay pedidos pendientes para eliminar en esta transferencia.');
            }

            $pedidosPendientesQuery->delete();

            if ($transferencia->pedidos()->count() === 0) {
                $transferencia->delete();
            }

            \DB::commit();
            return redirect()->route('admin.pedidos.pendientes')
                ->with('success', 'Pedidos pendientes eliminados correctamente.');

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->route('admin.pedidos.pendientes')
                ->with('error', 'Error al eliminar pedidos pendientes: ' . $e->getMessage());
        }
    }
}
