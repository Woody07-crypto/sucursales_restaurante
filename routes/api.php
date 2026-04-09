<?php

use App\Http\Controllers\Api\SucursalesController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PedidoController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API — tres flujos (ramas de trabajo: flow/sucursales, flow/catalogo, flow/pedidos)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

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
            'message' => 'Flujo catalogo operativo',
        ]));
        Route::middleware('auth:sanctum')->group(function () {
            Route::apiResource('products', ProductController::class);
        });
    });

    Route::prefix('pedidos')->group(function () {
        Route::get('/health', fn () => response()->json([
            'flow' => 'pedidos',
            'message' => 'Flujo pedidos operativo',
        ]));

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/', [PedidoController::class, 'index']);
            Route::post('/', [PedidoController::class, 'store']);
            Route::get('/{pedido}', [PedidoController::class, 'show']);
            Route::patch('/{pedido}/estado', [PedidoController::class, 'updateEstado']);
            Route::delete('/{pedido}', [PedidoController::class, 'destroy']);
        });
    });
});
