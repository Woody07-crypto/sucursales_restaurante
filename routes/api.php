<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\PedidosController;
use App\Http\Controllers\Api\SucursalesController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\OrdenCompraController;
use App\Http\Controllers\Api\V1\PedidoController as StockPedidoController;
use App\Http\Controllers\Api\V1\StockAnalyticsController;
use Illuminate\Support\Facades\Route;

/*
| API — tres flujos (ramas de trabajo: flow/sucursales, flow/catalogo, flow/pedidos)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::get('/sucursales/health', [SucursalesController::class, 'health']);
    Route::get('/catalogo/health', [CatalogoController::class, 'health']);
    Route::get('/pedidos/health', [PedidosController::class, 'health']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/sucursales', [SucursalesController::class, 'index']);
        Route::get('/sucursales/{id}', [SucursalesController::class, 'show'])->whereNumber('id');
        Route::post('/sucursales', [SucursalesController::class, 'store']);
        Route::put('/sucursales/{id}', [SucursalesController::class, 'update'])->whereNumber('id');
        Route::delete('/sucursales/{id}', [SucursalesController::class, 'destroy'])->whereNumber('id');

        Route::get('/catalogo/menu', [CatalogoController::class, 'menu']);
        Route::get('/catalogo/products', [CatalogoController::class, 'productsIndex']);
        Route::post('/catalogo/products', [CatalogoController::class, 'productsStore']);
        Route::put('/catalogo/products/{id}', [CatalogoController::class, 'productsUpdate'])->whereNumber('id');
        Route::delete('/catalogo/products/{id}', [CatalogoController::class, 'productsDestroy'])->whereNumber('id');

        Route::post('/pedidos', [PedidosController::class, 'store']);
        Route::get('/pedidos', [PedidosController::class, 'index']);
        Route::get('/pedidos/{pedido}', [PedidosController::class, 'show'])->whereNumber('pedido');
        Route::patch('/pedidos/{pedido}/estado', [PedidosController::class, 'updateEstado'])->whereNumber('pedido');
        Route::delete('/pedidos/{pedido}', [PedidosController::class, 'destroy'])->whereNumber('pedido');

        // Flujograma 3 (stock)
        Route::post('orders/purchase', [OrdenCompraController::class, 'suggested']);
        Route::post('orders', [StockPedidoController::class, 'store']);
        Route::post('purchase-orders', [OrdenCompraController::class, 'urgent']);
        Route::get('inventory/branch/{sucursal}', [InventoryController::class, 'byBranch'])
            ->where('sucursal', '[^/]+');
        Route::get('analytics/stock-alerts', [StockAnalyticsController::class, 'index']);
    });
});
