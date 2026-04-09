<?php

use App\Models\Sucursal;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('sucursales health incluye mensaje operativo', function () {
    $this->getJson('/api/v1/sucursales/health')
        ->assertOk()
        ->assertJsonPath('flow', 'sucursales')
        ->assertJsonPath('message', 'Servicio de sucursales operativo');
});

test('listar sucursales vacias', function () {
    $this->getJson('/api/v1/sucursales')
        ->assertOk()
        ->assertJsonPath('data', []);
});

test('crear y listar sucursal', function () {
    $payload = [
        'nombre' => 'Sucursal Centro',
        'direccion' => 'Calle Falsa 123',
        'ciudad' => 'Córdoba',
        'telefono' => '3515550000',
        'email' => 'centro@test.com',
        'horario' => 'Lun-Vie 9-18',
        'activa' => true,
    ];

    $this->postJson('/api/v1/sucursales', $payload)
        ->assertCreated()
        ->assertJsonPath('data.nombre', 'Sucursal Centro');

    $this->getJson('/api/v1/sucursales')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('crear sucursal validacion 422', function () {
    $this->postJson('/api/v1/sucursales', [])
        ->assertStatus(422);
});

test('mostrar actualizar y eliminar sucursal', function () {
    $s = Sucursal::factory()->create(['nombre' => 'Original']);

    $this->getJson("/api/v1/sucursales/{$s->id}")
        ->assertOk()
        ->assertJsonPath('data.nombre', 'Original');

    $this->patchJson("/api/v1/sucursales/{$s->id}", ['nombre' => 'Renombrada'])
        ->assertOk()
        ->assertJsonPath('data.nombre', 'Renombrada');

    $this->deleteJson("/api/v1/sucursales/{$s->id}")
        ->assertNoContent();

    $this->getJson("/api/v1/sucursales/{$s->id}")
        ->assertNotFound();
});

test('filtrar sucursales por activa', function () {
    Sucursal::factory()->create(['nombre' => 'A', 'activa' => true]);
    Sucursal::factory()->inactiva()->create(['nombre' => 'B']);

    $this->getJson('/api/v1/sucursales?activa=1')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('detalle sucursal 404', function () {
    $this->getJson('/api/v1/sucursales/99999')->assertNotFound();
});
