<?php

use App\Models\Ingrediente;
use App\Models\InventarioMovimiento;
use App\Models\Pedido;
use App\Models\ProductoIngrediente;
use App\Models\StockSucursalIngrediente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create(['role' => 'gerente']);
    Sanctum::actingAs($user);

    $masa = Ingrediente::create(['nombre' => 'Masa', 'slug' => 'masa', 'umbral' => 10]);
    $queso = Ingrediente::create(['nombre' => 'Queso', 'slug' => 'queso', 'umbral' => 5]);

    ProductoIngrediente::create([
        'producto_nombre' => 'Pizza',
        'ingrediente_id' => $masa->id,
        'cantidad_por_unidad' => 3,
    ]);
    ProductoIngrediente::create([
        'producto_nombre' => 'Pizza',
        'ingrediente_id' => $queso->id,
        'cantidad_por_unidad' => 2,
    ]);
});

test('pedido con stock ok responde 201 y sin alertas', function () {
    StockSucursalIngrediente::create(['sucursal' => 'Centro', 'ingrediente_id' => 1, 'cantidad' => 50]);
    StockSucursalIngrediente::create(['sucursal' => 'Centro', 'ingrediente_id' => 2, 'cantidad' => 30]);

    $this->postJson('/api/v1/orders', [
        'canal' => 'salon',
        'sucursal' => 'Centro',
        'items' => [
            ['nombre' => 'Pizza', 'cantidad' => 1, 'precio_unitario' => 12],
        ],
    ])
        ->assertStatus(201)
        ->assertJsonPath('stock.estado', 'ok')
        ->assertJsonPath('stock.alertas', []);

    expect(InventarioMovimiento::count())->toBe(2);
});

test('pedido con stock bajo umbral responde 207 y alerta warning', function () {
    StockSucursalIngrediente::create(['sucursal' => 'Centro', 'ingrediente_id' => 1, 'cantidad' => 12]);
    StockSucursalIngrediente::create(['sucursal' => 'Centro', 'ingrediente_id' => 2, 'cantidad' => 30]);

    $this->postJson('/api/v1/orders', [
        'canal' => 'salon',
        'sucursal' => 'Centro',
        'items' => [
            ['nombre' => 'Pizza', 'cantidad' => 1, 'precio_unitario' => 12],
        ],
    ])
        ->assertStatus(207)
        ->assertJsonPath('stock.estado', 'warning');

    expect(Pedido::count())->toBe(1);
});

test('pedido que agota ingrediente responde 201 critico y orden urgente', function () {
    StockSucursalIngrediente::create(['sucursal' => 'Centro', 'ingrediente_id' => 1, 'cantidad' => 2]);
    StockSucursalIngrediente::create(['sucursal' => 'Centro', 'ingrediente_id' => 2, 'cantidad' => 30]);

    $this->postJson('/api/v1/orders', [
        'canal' => 'salon',
        'sucursal' => 'Centro',
        'items' => [
            ['nombre' => 'Pizza', 'cantidad' => 1, 'precio_unitario' => 12],
        ],
    ])
        ->assertStatus(201)
        ->assertJsonPath('stock.estado', 'critical');

    $this->assertDatabaseHas('menu_items_bloqueados', [
        'sucursal' => 'Centro',
        'producto_nombre' => 'Pizza',
    ]);
});

test('inventory branch devuelve stock por sucursal', function () {
    StockSucursalIngrediente::create(['sucursal' => 'Centro', 'ingrediente_id' => 1, 'cantidad' => 5]);
    StockSucursalIngrediente::create(['sucursal' => 'Centro', 'ingrediente_id' => 2, 'cantidad' => 4]);

    $this->getJson('/api/v1/inventory/branch/'.rawurlencode('Centro'))
        ->assertOk()
        ->assertJsonPath('sucursal', 'Centro')
        ->assertJsonCount(2, 'items');
});
