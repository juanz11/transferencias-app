<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\TransferenciaPedidoController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\VisitadorController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;

// Rutas de autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    // Panel principal para visitador
    Route::get('/visitador', function () {
        return view('visitor.home');
    })->name('visitador.home');

    // Rutas de transferencias
    Route::get('/transferencias/reporte', [TransferenciaController::class, 'index'])->name('transferencias.reporte');
    Route::get('/transferencias', [TransferenciaController::class, 'index'])->name('transferencias.index');
    Route::get('/transferencias/confirmados', [TransferenciaController::class, 'listarConfirmados'])->name('transferencias.confirmados');
    Route::get('/transferencias/confirmados/{id}/edit', [TransferenciaController::class, 'editarConfirmada'])->name('transferencias.confirmados.edit');
    Route::put('/transferencias/confirmados/{id}', [TransferenciaController::class, 'actualizarConfirmada'])->name('transferencias.confirmados.update');
    Route::delete('/transferencias/confirmados/{id}', [TransferenciaController::class, 'eliminarConfirmada'])->name('transferencias.confirmados.destroy');

    // Rutas de pedidos
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/reporte', [PedidoController::class, 'reporte'])->name('pedidos.reporte');
    Route::post('/pedidos/enviar-reporte', [PedidoController::class, 'enviarReporteEmail'])->name('pedidos.enviar-reporte');

    // Rutas para crear pedidos y transferencias confirmadas
    Route::get('/transferencias/pedidos/create', [TransferenciaPedidoController::class, 'create'])->name('transferencias.pedidos.create');
    Route::post('/transferencias/pedidos', [TransferenciaPedidoController::class, 'store'])->name('transferencias.pedidos.store');

    // Rutas para visitadores
    Route::resource('visitadores', VisitadorController::class)->parameters([
        'visitadores' => 'visitador'
    ]);

    // Rutas para productos
    Route::resource('productos', ProductoController::class)->parameters([
        'productos' => 'producto'
    ]);

    // Rutas para clientes
    Route::resource('clientes', ClienteController::class)->parameters([
        'clientes' => 'cliente'
    ]);
});
