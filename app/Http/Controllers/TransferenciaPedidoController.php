<?php

namespace App\Http\Controllers;

use App\Mail\TransferenciaConfirmada;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Visitador;
use App\Models\Transferencia;
use App\Models\TransferenciaConfirmada as TransferenciaConfirmadaModel;
use App\Models\PedidoConfirmado;
use App\Models\Drogeria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;

class TransferenciaPedidoController extends Controller
{
    public function create()
    {
        $visitadores = Visitador::all();
        $clientes = Cliente::all();
        $productos = Producto::all();
        
        return view('transferencias.create-pedido', compact('visitadores', 'clientes', 'productos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'visitador_id' => 'required|exists:visitadores,id',
            'codigo_cliente' => 'required|exists:clientes,codigo_cliente',
            'fecha_correo' => 'required|date',
            'fecha_transferencia' => 'required|date',
            'transferencia_numero' => 'required|string|unique:transferencias,transferencia_numero',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|numeric|min:1',
            'productos.*.descuento' => 'nullable|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $cliente = Cliente::where('codigo_cliente', $request->codigo_cliente)->firstOrFail();
            $visitador = Visitador::findOrFail($request->visitador_id);
            $drogueria = Drogeria::findOrFail($cliente->drogueria);

            // Crear la transferencia
            $transferencia = new Transferencia([
                'user_id' => auth()->id(),
                'visitador_id' => $request->visitador_id,
                'cliente_id' => $cliente->id,
                'fecha_correo' => $request->fecha_correo,
                'fecha_transferencia' => $request->fecha_transferencia,
                'transferencia_numero' => $request->transferencia_numero,
                'confirmada' => true
            ]);
            $transferencia->save();

            // Crear la transferencia confirmada
            $transferenciaConfirmada = new TransferenciaConfirmadaModel([
                'user_id' => auth()->id(),
                'transferencia_id' => $transferencia->id
            ]);
            $transferenciaConfirmada->save();

            // Crear los pedidos confirmados y calcular comisiones
            $calculos = [];
            foreach ($request->productos as $productoData) {
                $producto = Producto::findOrFail($productoData['id']);
                
                $pedidoConfirmado = new PedidoConfirmado([
                    'transferencia_confirmada_id' => $transferenciaConfirmada->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $productoData['cantidad'],
                    'descuento' => $productoData['descuento'] ?? 0
                ]);
                $pedidoConfirmado->save();

                $calculos[] = (object)[
                    'productos' => $producto,
                    'cantidad' => $productoData['cantidad'],
                    'comision' => $producto->comision,
                    'total' => $productoData['cantidad'] * $producto->comision
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
                Mail::to($recipients)->send(new TransferenciaConfirmada($transferenciaConfirmada, $calculos, $drogueria));
            } catch (\Exception $mailError) {
                \Log::error('Error al enviar correo: ' . $mailError->getMessage());
                throw $mailError;
            }

            DB::commit();
            return redirect()->route('transferencias.index')
                ->with('success', 'Pedido y transferencia confirmada creados exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Error al crear el pedido: ' . $e->getMessage());
        }
    }
}
