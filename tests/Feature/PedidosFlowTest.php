<?php

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function authAs(string $role): void
{
    $user = User::factory()->create(['role' => $role]);
    Sanctum::actingAs($user);
}

test('can create pedido with valid payload', function () {
    authAs('cajero');

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
    authAs('cajero');

    $this->postJson('/api/v1/pedidos', [
        'canal' => 'desconocido',
        'sucursal' => 'Centro',
    ])->assertStatus(422);
});

test('can filter pedidos by estado', function () {
    authAs('cajero');

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
    authAs('cajero');

    $this->getJson('/api/v1/pedidos/99999')->assertNotFound();
});

test('updates pedido status with valid transition', function () {
    authAs('gerente');

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
    authAs('gerente');

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
    authAs('admin');

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

test('requires authentication for pedidos endpoints', function () {
    $this->getJson('/api/v1/pedidos')->assertStatus(401);
});

test('forbids cashier from changing status', function () {
    authAs('cajero');

    $pedido = Pedido::create([
        'codigo' => 'PED-TEST-006',
        'cliente_nombre' => 'F',
        'canal' => 'delivery',
        'sucursal' => 'Centro',
        'estado' => 'pendiente',
        'total' => 5,
        'items' => [['nombre' => 'Taco', 'cantidad' => 1, 'precio_unitario' => 5]],
    ]);

    $this->patchJson("/api/v1/pedidos/{$pedido->id}/estado", ['estado' => 'en_preparacion'])
        ->assertStatus(403);
});

test('forbids non admin deleting pedido', function () {
    authAs('gerente');

    $pedido = Pedido::create([
        'codigo' => 'PED-TEST-007',
        'cliente_nombre' => 'G',
        'canal' => 'salon',
        'sucursal' => 'Centro',
        'estado' => 'pendiente',
        'total' => 9,
        'items' => [['nombre' => 'Sandwich', 'cantidad' => 1, 'precio_unitario' => 9]],
    ]);

    $this->deleteJson("/api/v1/pedidos/{$pedido->id}")
        ->assertStatus(403);
});
