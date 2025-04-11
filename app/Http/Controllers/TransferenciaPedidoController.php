<?php

namespace App\Http\Controllers;

use App\Models\Transferencia;
use App\Models\Pedido;
use App\Models\PedidoConfirmado;
use App\Models\TransferenciaConfirmada;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Visitador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

            // Crear la transferencia
            $transferencia = Transferencia::create([
                'user_id' => 1, // Usuario fijo con ID 1
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
                'user_id' => 1, // Usuario fijo con ID 1
                'transferencia_id' => $transferencia->id
            ]);

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
                PedidoConfirmado::create([
                    'transferencia_confirmada_id' => $transferenciaConfirmada->id,
                    'producto_id' => $producto['id'],
                    'cantidad' => $producto['cantidad'],
                    'descuento' => $producto['descuento'] ?? 0
                ]);
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
