<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\TransferenciaPedidoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/transferencias', [TransferenciaController::class, 'index'])->name('transferencias.index');
Route::get('/transferencias/reporte', [TransferenciaController::class, 'reporteTransferencias'])->name('transferencias.reporte');

Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
Route::get('/pedidos/reporte', [PedidoController::class, 'reportePedidos'])->name('pedidos.reporte');

// Rutas para crear pedidos y transferencias confirmadas
Route::get('/transferencias/pedidos/create', [TransferenciaPedidoController::class, 'create'])->name('transferencias.pedidos.create');
Route::post('/transferencias/pedidos', [TransferenciaPedidoController::class, 'store'])->name('transferencias.pedidos.store');
