<?php

test('docs api page is available', function () {
    $this->get('/docs/api')->assertStatus(200);
});

test('openapi spec is served', function () {
    $this->get('/openapi.yaml')->assertStatus(200);
});

test('sucursales flow health', function () {
    $this->getJson('/api/v1/sucursales/health')
        ->assertOk()
        ->assertJsonPath('flow', 'sucursales');
});

test('catalogo flow health', function () {
    $this->getJson('/api/v1/catalogo/health')
        ->assertOk()
        ->assertJsonPath('flow', 'catalogo');
});

test('pedidos flow health', function () {
    $this->getJson('/api/v1/pedidos/health')
        ->assertOk()
        ->assertJsonPath('flow', 'pedidos');
});
