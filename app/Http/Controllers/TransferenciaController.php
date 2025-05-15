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
        // Obtener lista de visitadores y droguerías para los selectores
        $visitadores = Visitador::orderBy('nombre')->get();
        $droguerias = Drogeria::orderBy('nombre')->get();

        $query = PedidoConfirmado::with([
            'transferenciaConfirmada.transferencia.visitador',
            'producto'
        ]);

        // Filtrar por fecha específica (fecha de confirmación)
        if ($request->fecha) {
            $fecha = Carbon::createFromFormat('Y-m-d', $request->fecha);
            $query->whereHas('transferenciaConfirmada', function($q) use ($fecha) {
                $q->whereDate('created_at', $fecha);
            });
        }

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

        foreach ($pedidos->groupBy('transferenciaConfirmada.id') as $confirmacionId => $pedidosGroup) {
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
        $request->validate([
            'fecha_transferencia' => 'required|date',
            'fecha_confirmacion' => 'required|date',
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

            // Enviar el email al visitador y a todos los usuarios
            $recipients = collect();
            
            // Agregar el email del visitador si existe
            \Log::info('Email del visitador: ' . ($visitador->email ?? 'No tiene email'));
            if ($visitador->email) {
                $recipients->push($visitador->email);
            }
            
            // Agregar los emails de todos los usuarios
            $userEmails = User::whereNotNull('email')->pluck('email');
            \Log::info('Emails de usuarios encontrados: ' . $userEmails->join(', '));
            
            $recipients = $recipients->merge($userEmails)->unique();
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
}
