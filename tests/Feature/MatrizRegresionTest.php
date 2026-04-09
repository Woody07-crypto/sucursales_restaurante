<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

test('MAT-X-001 rutas Laravel cubren operaciones documentadas en OpenAPI', function () {
    $operations = [
        ['POST', '/api/v1/auth/login'],
        ['POST', '/api/v1/auth/logout'],
        ['GET', '/api/v1/sucursales/health'],
        ['GET', '/api/v1/catalogo/health'],
        ['GET', '/api/v1/pedidos/health'],
        ['GET', '/api/v1/sucursales'],
        ['POST', '/api/v1/sucursales'],
        ['GET', '/api/v1/sucursales/1'],
        ['PUT', '/api/v1/sucursales/1'],
        ['DELETE', '/api/v1/sucursales/1'],
        ['GET', '/api/v1/catalogo/menu'],
        ['POST', '/api/v1/pedidos'],
    ];

    foreach ($operations as [$method, $uri]) {
        $request = Request::create($uri, $method);
        Route::getRoutes()->match($request);
    }

    expect(true)->toBeTrue();
});

test('MAT-X-002 documentación Swagger carga y apunta al OpenAPI', function () {
    $this->get('/docs/api')
        ->assertOk()
        ->assertSee('swagger-ui', false)
        ->assertSee('/openapi.yaml', false);
});
