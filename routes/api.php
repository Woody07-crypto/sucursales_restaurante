<?php

use App\Http\Controllers\Api\SucursalesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API — tres flujos (ramas de trabajo: flow/sucursales, flow/catalogo, flow/pedidos)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::prefix('sucursales')->group(function () {
        Route::get('/health', [SucursalesController::class, 'health']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/', [SucursalesController::class, 'index']);
            Route::post('/', [SucursalesController::class, 'store']);
            Route::get('/{sucursal}', [SucursalesController::class, 'show']);
            Route::put('/{sucursal}', [SucursalesController::class, 'update']);
            Route::patch('/{sucursal}', [SucursalesController::class, 'update']);
            Route::delete('/{sucursal}', [SucursalesController::class, 'destroy']);
        });
    });

    Route::prefix('catalogo')->group(function () {
        Route::get('/health', fn () => response()->json([
            'flow' => 'catalogo',
            'message' => 'Placeholder: implementar en rama flow/catalogo',
        ]));
    });

    Route::prefix('pedidos')->group(function () {
        Route::get('/health', fn () => response()->json([
            'flow' => 'pedidos',
            'message' => 'Placeholder: implementar en rama flow/pedidos',
        ]));
    });
});
