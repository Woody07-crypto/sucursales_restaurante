<?php

use Illuminate\Support\Facades\Route;

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
