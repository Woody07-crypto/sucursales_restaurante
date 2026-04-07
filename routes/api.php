<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
/*
|--------------------------------------------------------------------------
| API — tres flujos (ramas de trabajo: flow/sucursales, flow/catalogo, flow/pedidos)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::prefix('sucursales')->group(function () {
        Route::get('/health', fn () => response()->json([
            'flow' => 'sucursales',
            'message' => 'Placeholder: implementar en rama flow/sucursales',
        ]));
    });

    

      Route::prefix('catalogo')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
     });

    Route::prefix('pedidos')->group(function () {
        Route::get('/health', fn () => response()->json([
            'flow' => 'pedidos',
            'message' => 'Placeholder: implementar en rama flow/pedidos',
        ]));
    });
});
