<?php

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('unauthenticated user gets 401', function () {
    $this->getJson('/api/v1/catalogo/products')->assertUnauthorized();
});

test('authenticated user can list products', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/catalogo/products')->assertOk();
});

test('authenticated user can create product', function () {
    $sucursal = Sucursal::factory()->create();

    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/catalogo/products', [
        'nombre' => 'Pizza',
        'precio' => 9.99,
        'sucursal_id' => $sucursal->id,
    ])->assertCreated();
});

test('invalid data returns 422', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/v1/catalogo/products', [])->assertStatus(422);
});

test('update nonexistent product returns 404', function () {
    $sucursal = Sucursal::factory()->create();

    Sanctum::actingAs(User::factory()->create());

    $this->putJson('/api/v1/catalogo/products/9999', [
        'nombre' => 'Burger',
        'precio' => 5.99,
        'sucursal_id' => $sucursal->id,
    ])->assertNotFound();
});

test('delete nonexistent product returns 404', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->deleteJson('/api/v1/catalogo/products/9999')->assertNotFound();
});
