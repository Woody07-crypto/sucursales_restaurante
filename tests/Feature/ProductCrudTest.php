<?php

use App\Models\User;
use App\Models\Product;

test('unauthenticated user gets 401', function () {
    $response = $this->getJson('/api/v1/catalogo/products');
    $response->assertStatus(401);
});

test('authenticated user can list products', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->getJson('/api/v1/catalogo/products');
    $response->assertStatus(200);
});

test('authenticated user can create product', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson('/api/v1/catalogo/products', [
        'nombre' => 'Pizza',
        'precio' => 9.99,
        'sucursal_id' => 1,
    ]);
    $response->assertStatus(201);
});

test('invalid data returns 422', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson('/api/v1/catalogo/products', []);
    $response->assertStatus(422);
});

test('update nonexistent product returns 404', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->putJson('/api/v1/catalogo/products/9999', [
        'nombre' => 'Burger',
        'precio' => 5.99,
        'sucursal_id' => 1,
    ]);
    $response->assertStatus(404);
});

test('delete nonexistent product returns 404', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->deleteJson('/api/v1/catalogo/products/9999');
    $response->assertStatus(404);
});