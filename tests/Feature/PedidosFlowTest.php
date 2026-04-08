<?php

use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create pedido with valid payload', function () {
    $payload = [
        'cliente_nombre' => 'Cliente Demo',
        'canal' => 'delivery',
        'sucursal' => 'Santa Tecla',
        'items' => [
            ['nombre' => 'Pizza', 'cantidad' => 2, 'precio_unitario' => 7.50],
            ['nombre' => 'Soda', 'cantidad' => 1, 'precio_unitario' => 1.75],
        ],
        'notas' => 'Sin hielo',
    ];

    $this->postJson('/api/v1/pedidos', $payload)
        ->assertCreated()
        ->assertJsonPath('data.canal', 'delivery')
        ->assertJsonPath('data.estado', 'pendiente')
        ->assertJsonPath('data.total', 16.75);
});

test('validates pedido creation payload', function () {
    $this->postJson('/api/v1/pedidos', [
        'canal' => 'desconocido',
        'sucursal' => 'Centro',
    ])->assertStatus(422);
});

test('can filter pedidos by estado', function () {
    Pedido::create([
        'codigo' => 'PED-TEST-001',
        'cliente_nombre' => 'A',
        'canal' => 'salon',
        'sucursal' => 'Centro',
        'estado' => 'pendiente',
        'total' => 10,
        'items' => [['nombre' => 'Combo', 'cantidad' => 1, 'precio_unitario' => 10]],
    ]);

    Pedido::create([
        'codigo' => 'PED-TEST-002',
        'cliente_nombre' => 'B',
        'canal' => 'delivery',
        'sucursal' => 'Centro',
        'estado' => 'cancelado',
        'total' => 8,
        'items' => [['nombre' => 'Hamburguesa', 'cantidad' => 1, 'precio_unitario' => 8]],
    ]);

    $this->getJson('/api/v1/pedidos?estado=pendiente')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.estado', 'pendiente');
});

test('returns 404 when pedido does not exist', function () {
    $this->getJson('/api/v1/pedidos/99999')->assertNotFound();
});

test('updates pedido status with valid transition', function () {
    $pedido = Pedido::create([
        'codigo' => 'PED-TEST-003',
        'cliente_nombre' => 'C',
        'canal' => 'take-away',
        'sucursal' => 'Escalon',
        'estado' => 'pendiente',
        'total' => 6,
        'items' => [['nombre' => 'Cafe', 'cantidad' => 2, 'precio_unitario' => 3]],
    ]);

    $this->patchJson("/api/v1/pedidos/{$pedido->id}/estado", ['estado' => 'en_preparacion'])
        ->assertOk()
        ->assertJsonPath('data.estado', 'en_preparacion');
});

test('rejects invalid status transition', function () {
    $pedido = Pedido::create([
        'codigo' => 'PED-TEST-004',
        'cliente_nombre' => 'D',
        'canal' => 'salon',
        'sucursal' => 'Merliot',
        'estado' => 'cancelado',
        'total' => 6,
        'items' => [['nombre' => 'Papas', 'cantidad' => 1, 'precio_unitario' => 6]],
    ]);

    $this->patchJson("/api/v1/pedidos/{$pedido->id}/estado", ['estado' => 'listo'])
        ->assertStatus(422);
});

test('prevents deleting delivered pedido', function () {
    $pedido = Pedido::create([
        'codigo' => 'PED-TEST-005',
        'cliente_nombre' => 'E',
        'canal' => 'delivery',
        'sucursal' => 'Centro',
        'estado' => 'entregado',
        'total' => 4,
        'items' => [['nombre' => 'Postre', 'cantidad' => 1, 'precio_unitario' => 4]],
    ]);

    $this->deleteJson("/api/v1/pedidos/{$pedido->id}")
        ->assertStatus(409);
});
