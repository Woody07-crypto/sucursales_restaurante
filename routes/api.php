<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\PedidosController;
use App\Http\Controllers\Api\SucursalesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API — tres flujos (ramas: flow/sucursales, flow/catalogo, flow/pedidos)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::get('/sucursales/health', [SucursalesController::class, 'health']);
    Route::get('/catalogo/health', [CatalogoController::class, 'health']);
    Route::get('/pedidos/health', [PedidosController::class, 'health']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/sucursales', [SucursalesController::class, 'index']);
        Route::get('/sucursales/{id}', [SucursalesController::class, 'show'])->whereNumber('id');
        Route::post('/sucursales', [SucursalesController::class, 'store']);
        Route::put('/sucursales/{id}', [SucursalesController::class, 'update'])->whereNumber('id');
        Route::delete('/sucursales/{id}', [SucursalesController::class, 'destroy'])->whereNumber('id');

        Route::get('/catalogo/menu', [CatalogoController::class, 'menu']);
        Route::post('/pedidos', [PedidosController::class, 'store']);
    });
});
