<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\OrdenCompraController;
use App\Http\Controllers\Api\V1\PedidoController;
use App\Http\Controllers\Api\V1\StockAnalyticsController;
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
        Route::get('/health', fn () => response()->json([
            'flow' => 'sucursales',
            'message' => 'Placeholder: implementar en rama flow/sucursales',
        ]));
    });

    Route::prefix('catalogo')->group(function () {
        Route::get('/health', fn () => response()->json([
            'flow' => 'catalogo',
            'message' => 'Placeholder: implementar en rama flow/catalogo',
        ]));
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('orders/purchase', [OrdenCompraController::class, 'suggested']);
        Route::post('orders', [PedidoController::class, 'store']);
        Route::post('purchase-orders', [OrdenCompraController::class, 'urgent']);
        Route::get('inventory/branch/{sucursal}', [InventoryController::class, 'byBranch'])
            ->where('sucursal', '[^/]+');
        Route::get('analytics/stock-alerts', [StockAnalyticsController::class, 'index']);
    });

    Route::prefix('pedidos')->group(function () {
        Route::get('/health', fn () => response()->json([
            'flow' => 'pedidos',
            'message' => 'Flujo pedidos operativo (incluye integración stock — Flujograma 3)',
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
