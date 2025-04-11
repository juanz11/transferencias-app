<?php

namespace App\Http\Controllers;

use App\Models\Transferencia;
use App\Models\Pedido;
use App\Models\PedidoConfirmado;
use App\Models\TransferenciaConfirmada;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Visitador;
use App\Mail\TransferenciaConfirmada as TransferenciaConfirmadaMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
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

        try {
            DB::beginTransaction();

            // Obtener el cliente por código_cliente
            $cliente = Cliente::where('codigo_cliente', $request->codigo_cliente)->firstOrFail();
            
            // Obtener el visitador para el email
            $visitador = Visitador::findOrFail($request->visitador_id);

            // Crear la transferencia
            $transferencia = Transferencia::create([
                'user_id' => auth()->id(),
                'visitador_id' => $request->visitador_id,
                'cliente_id' => $cliente->id,
                'codigo_cliente' => $request->codigo_cliente,
                'fecha_correo' => $request->fecha_correo,
                'fecha_transferencia' => $request->fecha_transferencia,
                'transferencia_numero' => $request->transferencia_numero,
                'fecha_carga' => Carbon::now(),
                'confirmada' => true
            ]);

            // Crear la transferencia confirmada
            $transferenciaConfirmada = TransferenciaConfirmada::create([
                'user_id' => auth()->id(),
                'transferencia_id' => $transferencia->id
            ]);

            $calculos = [];
            // Crear los pedidos y pedidos confirmados
            foreach ($request->productos as $producto) {
                // Crear pedido
                $pedido = Pedido::create([
                    'transferencia_id' => $transferencia->id,
                    'producto_id' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'descuento' => $producto['descuento'] ?? 0
                ]);

                // Crear pedido confirmado
                $pedidoConfirmado = PedidoConfirmado::create([
                    'transferencia_confirmada_id' => $transferenciaConfirmada->id,
                    'producto_id' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'descuento' => $producto['descuento'] ?? 0
                ]);

                // Agregar a los cálculos para el email
                $productoInfo = Producto::find($producto['id']);
                $calculo = new \stdClass();
                $calculo->productos = $productoInfo;
                $calculo->cantidad = $producto['cantidad'];
                $calculo->comision = $productoInfo->comision ?? 0;
                $calculo->total = ($producto['cantidad'] * $productoInfo->comision);
                $calculos[] = $calculo;
            }

            // Obtener la droguería (ajusta esto según tu estructura)
            $drogueria = (object)['nombre' => 'Tu Droguería']; // Reemplaza esto con la obtención real de la droguería

            // Enviar el email
            if ($visitador->email) {
                Mail::to($visitador->email)->send(new TransferenciaConfirmadaMail($transferenciaConfirmada, $calculos, $drogueria));
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
