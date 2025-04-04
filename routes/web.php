<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\PedidoController;

// Desactivar sesiones para estas rutas
config(['session.driver' => 'array']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/transferencias', [TransferenciaController::class, 'index'])->name('transferencias.index');
Route::get('/transferencias/reporte', [TransferenciaController::class, 'reporteTransferencias'])->name('transferencias.reporte');

Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
Route::get('/pedidos/reporte', [PedidoController::class, 'reportePedidos'])->name('pedidos.reporte');
