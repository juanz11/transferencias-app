<?php

namespace App\Http\Controllers;

use App\Mail\TransferenciaConfirmada as TransferenciaConfirmadaMail;
use App\Models\Transferencia;
use App\Models\TransferenciaConfirmada;
use App\Models\PedidoConfirmado;
use App\Models\Visitador;
use App\Models\Producto;
use App\Models\User;
use App\Models\Drogeria;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class TransferenciaController extends Controller
{
    public function index()
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $query = Transferencia::with('visitador')
            ->join('transferencias_confirmadas', 'transferencias.id', '=', 'transferencias_confirmadas.transferencia_id')
            ->select(
                'transferencias.*',
                'transferencias_confirmadas.created_at as fecha_confirmacion',
                'transferencias_confirmadas.factura_path'
            );

        // Filtrar por fecha específica
        if (request()->fecha) {
            $fecha = Carbon::createFromFormat('Y-m-d', request()->fecha);
            $query->whereDate('transferencias_confirmadas.created_at', $fecha);
        }

        $transferencias = $query->orderBy('transferencias_confirmadas.created_at', 'desc')
            ->get();

        return view('transferencias.reporte', compact('transferencias'));
    }

    public function listarConfirmados(Request $request)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        // Obtener lista de visitadores y droguerías para los selectores
        $visitadores = Visitador::orderBy('nombre')->get();
        $droguerias = Drogeria::orderBy('nombre')->get();

        $query = PedidoConfirmado::with([
            'transferenciaConfirmada.transferencia.visitador',
            'producto'
        ])
        ->join('transferencias_confirmadas', 'pedidos_confirmados.transferencia_confirmada_id', '=', 'transferencias_confirmadas.id')
        ->orderBy('transferencias_confirmadas.created_at', 'desc');

        // Se eliminó el filtro por fecha ya que ahora mostraremos todas las transferencias ordenadas por fecha de confirmación

        // Filtrar por visitador
        if ($request->visitador_id) {
            $query->whereHas('transferenciaConfirmada.transferencia', function($q) use ($request) {
                $q->where('visitador_id', $request->visitador_id);
            });
        }

        // Filtrar por número de transferencia
        if ($request->transferencia_numero) {
            $query->whereHas('transferenciaConfirmada.transferencia', function($q) use ($request) {
                $q->where('transferencia_numero', 'LIKE', '%' . $request->transferencia_numero . '%');
            });
        }

        // Filtrar por droguería
        if ($request->drogueria_id) {
            $query->whereHas('transferenciaConfirmada.transferencia.cliente', function($q) use ($request) {
                $q->where('drogueria', $request->drogueria_id);
            });
        }

        // Agrupar pedidos por transferencia
        $pedidos = $query->get();

        // Debug: Verificar si hay pedidos
        \Log::info('Número de pedidos encontrados: ' . $pedidos->count());

        $transferencias = collect();

        // Ordenar los pedidos por fecha de confirmación descendente antes de agrupar
        $pedidosOrdenados = $pedidos->sortByDesc('transferenciaConfirmada.created_at');

        foreach ($pedidosOrdenados->groupBy('transferenciaConfirmada.id') as $confirmacionId => $pedidosGroup) {
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

        return view('transferencias.confirmados', compact('transferencias', 'visitadores', 'droguerias'));
    }

    public function editarConfirmada($id)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

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
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        $request->validate([
            'fecha_transferencia' => 'required|date',
            'fecha_confirmacion' => 'required|date_format:Y-m-d\TH:i',
            'transferencia_numero' => 'required|string',
            'visitador_id' => 'required|exists:visitadores,id',
            'productos' => 'required|array|min:1',
            'productos.*' => 'required|exists:productos,id',
            'cantidades' => 'required|array|min:1',
            'cantidades.*' => 'required|integer|min:1',
            'descuentos' => 'required|array|min:1',
            'descuentos.*' => 'required|integer|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $transferenciaConfirmada = TransferenciaConfirmada::with('transferencia')->findOrFail($id);
            $visitador = Visitador::findOrFail($request->visitador_id);
            $drogueria = Drogeria::findOrFail($transferenciaConfirmada->transferencia->cliente->drogueria);

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

            // Crear los nuevos pedidos confirmados y calcular comisiones
            $calculos = [];
            foreach ($request->productos as $index => $productoId) {
                $producto = Producto::findOrFail($productoId);

                $pedidoConfirmado = PedidoConfirmado::create([
                    'transferencia_confirmada_id' => $transferenciaConfirmada->id,
                    'producto_id' => $productoId,
                    'cantidad' => $request->cantidades[$index],
                    'descuento' => $request->descuentos[$index],
                ]);

                $calculos[] = (object)[
                    'productos' => $producto,
                    'cantidad' => $request->cantidades[$index],
                    'comision' => $producto->comision,
                    'total' => $request->cantidades[$index] * $producto->comision
                ];
            }

            // Enviar el email solo al visitador y a los usuarios con rol admin
            $recipients = collect();

            // Agregar el email del visitador si existe
            \Log::info('Email del visitador: ' . ($visitador->email ?? 'No tiene email'));
            if ($visitador->email) {
                $recipients->push($visitador->email);
            }

            // Agregar los emails de los usuarios administradores
            $adminEmails = User::where('rol', 'admin')
                ->whereNotNull('email')
                ->pluck('email');
            \Log::info('Emails de usuarios administradores encontrados: ' . $adminEmails->join(', '));

            $recipients = $recipients->merge($adminEmails)->unique();
            \Log::info('Lista final de destinatarios: ' . $recipients->join(', '));

            // Enviar el correo a todos los destinatarios
            try {
                \Log::info('Intentando enviar correo a: ' . $recipients->join(', '));
                Mail::to($recipients)->send(new TransferenciaConfirmadaMail($transferenciaConfirmada, $calculos, $drogueria));
            } catch (\Exception $mailError) {
                \Log::error('Error al enviar correo: ' . $mailError->getMessage());
                throw $mailError;
            }

            DB::commit();
            return redirect()->route('transferencias.confirmados')
                ->with('success', 'Transferencia confirmada actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Error al actualizar la transferencia: ' . $e->getMessage());
        }
    }

    public function eliminarConfirmada($id)
    {
        if (!auth()->check() || auth()->user()->rol !== 'admin') {
            return redirect()->route('visitador.home');
        }

        DB::beginTransaction();
        try {
            $transferenciaConfirmada = TransferenciaConfirmada::with('transferencia')->findOrFail($id);
            
            // Eliminar los pedidos confirmados relacionados
            $transferenciaConfirmada->pedidosConfirmados()->delete();
            
            // Eliminar la transferencia confirmada
            $transferenciaConfirmada->delete();
            
            // Eliminar la transferencia
            $transferenciaConfirmada->transferencia->delete();
            
            DB::commit();
            return redirect()->route('transferencias.confirmados')
                ->with('success', 'Transferencia eliminada exitosamente');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al eliminar la transferencia: ' . $e->getMessage());
        }
    }
}
