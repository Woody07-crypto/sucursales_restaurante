<?php

use App\Models\Product;
use App\Models\Sucursal;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('MAT-CAT-001 health catálogo sin token', function () {
    $this->getJson('/api/v1/catalogo/health')
        ->assertOk()
        ->assertJsonPath('flow', 'catalogo');
});

test('MAT-CAT-002 menú productos activos por rol', function () {
    $a = Sucursal::factory()->create();
    $b = Sucursal::factory()->create();

    Product::factory()->create(['sucursal_id' => $a->id, 'nombre' => 'Plato A', 'activo' => true]);
    Product::factory()->inactive()->create(['sucursal_id' => $a->id, 'nombre' => 'Oculto A']);
    Product::factory()->create(['sucursal_id' => $b->id, 'nombre' => 'Plato B', 'activo' => true]);

    $gg = User::factory()->gerenteGlobal()->create();
    Sanctum::actingAs($gg);
    $this->getJson('/api/v1/catalogo/menu')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $gs = User::factory()->gerenteSucursal($a->id)->create();
    Sanctum::actingAs($gs);
    $names = collect($this->getJson('/api/v1/catalogo/menu')->json('data'))->pluck('nombre');
    expect($names)->toHaveCount(1)->and($names->first())->toBe('Plato A');
});
