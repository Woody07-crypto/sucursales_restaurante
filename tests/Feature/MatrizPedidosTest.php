<?php

use App\Models\Pedido;
use App\Models\Product;
use App\Models\Sucursal;
use App\Models\User;

test('MAT-PED-001 health pedidos sin token', function () {
    $this->getJson('/api/v1/pedidos/health')
        ->assertOk()
        ->assertJsonPath('flow', 'pedidos');
});

test('MAT-PED-002 crear pedido 201', function () {
    $s = Sucursal::factory()->create(['nombre' => 'Sucursal Test']);
    $p = Product::factory()->create(['sucursal_id' => $s->id, 'precio' => 5.5, 'activo' => true]);
    $gg = User::factory()->gerenteGlobal()->create();

    $this->postJson('/api/v1/pedidos', [
        'canal' => 'mostrador',
        'sucursal' => 'Sucursal Test',
        'items' => [
            ['product_id' => $p->id, 'cantidad' => 2],
        ],
        'cliente_nombre' => 'Cliente QA',
    ], apiBearer($gg))
        ->assertCreated()
        ->assertJsonPath('estado', 'pendiente');

    expect(Pedido::query()->count())->toBe(1);
});
