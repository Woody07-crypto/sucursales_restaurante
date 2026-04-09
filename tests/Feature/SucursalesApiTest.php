<?php

use App\Models\AuditLog;
use App\Models\Pedido;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('sucursales health incluye mensaje operativo', function () {
    $this->getJson('/api/v1/sucursales/health')
        ->assertOk()
        ->assertJsonPath('flow', 'sucursales')
        ->assertJsonPath('message', 'Servicio de sucursales operativo');
});

test('listar sucursales sin token responde 401', function () {
    $this->getJson('/api/v1/sucursales')->assertUnauthorized();
});

test('listar sucursales vacias', function () {
    Sanctum::actingAs(User::factory()->gerenteGeneral()->create());

    $this->getJson('/api/v1/sucursales')
        ->assertOk()
        ->assertJsonPath('data', []);
});

test('crear y listar sucursal', function () {
    Sanctum::actingAs(User::factory()->gerenteGeneral()->create());

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
    Sanctum::actingAs(User::factory()->gerenteGeneral()->create());

    $this->postJson('/api/v1/sucursales', [])
        ->assertStatus(422);
});

test('gerente sucursal no puede crear sucursal 403', function () {
    Sanctum::actingAs(User::factory()->gerenteSucursal()->create());

    $this->postJson('/api/v1/sucursales', [
        'nombre' => 'Nueva',
        'direccion' => 'Calle 1',
        'ciudad' => 'X',
        'telefono' => '12345678',
    ])->assertForbidden();
});

test('nombre duplicado responde 409', function () {
    Sanctum::actingAs(User::factory()->gerenteGeneral()->create());

    $payload = [
        'nombre' => 'Única',
        'direccion' => 'A',
        'ciudad' => 'B',
        'telefono' => '12345678',
    ];

    $this->postJson('/api/v1/sucursales', $payload)->assertCreated();

    $this->postJson('/api/v1/sucursales', $payload)->assertStatus(409);
});

test('mostrar actualizar y eliminar sucursal', function () {
    Sanctum::actingAs(User::factory()->gerenteGeneral()->create());

    $s = Sucursal::factory()->create(['nombre' => 'Original']);

    $this->getJson("/api/v1/sucursales/{$s->id}")
        ->assertOk()
        ->assertJsonPath('data.nombre', 'Original')
        ->assertJsonStructure(['data' => ['kpis' => ['total_pedidos', 'pedidos_activos', 'total_ventas', 'stock_resumen']]]);

    $this->patchJson("/api/v1/sucursales/{$s->id}", ['nombre' => 'Renombrada'])
        ->assertOk()
        ->assertJsonPath('data.nombre', 'Renombrada');

    expect(AuditLog::query()->where('action', 'updated')->count())->toBe(1);

    $this->deleteJson("/api/v1/sucursales/{$s->id}")
        ->assertNoContent();

    expect(AuditLog::query()->where('action', 'deleted')->count())->toBe(1);

    $this->getJson("/api/v1/sucursales/{$s->id}")
        ->assertNotFound();
});

test('filtrar sucursales por activa', function () {
    Sanctum::actingAs(User::factory()->gerenteGeneral()->create());

    Sucursal::factory()->create(['nombre' => 'A', 'activa' => true]);
    Sucursal::factory()->inactiva()->create(['nombre' => 'B']);

    $this->getJson('/api/v1/sucursales?activa=1')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('detalle sucursal 404', function () {
    Sanctum::actingAs(User::factory()->gerenteGeneral()->create());

    $this->getJson('/api/v1/sucursales/99999')->assertNotFound();
});

test('gerente sucursal no ve detalle de otra sucursal 403', function () {
    $mia = Sucursal::factory()->create(['nombre' => 'Mía']);
    $otra = Sucursal::factory()->create(['nombre' => 'Otra']);

    Sanctum::actingAs(User::factory()->gerenteSucursal($mia->id)->create());

    $this->getJson("/api/v1/sucursales/{$otra->id}")->assertForbidden();
});

test('gerente sucursal solo lista su sucursal', function () {
    $mia = Sucursal::factory()->create(['nombre' => 'Mía']);
    Sucursal::factory()->create(['nombre' => 'Otra']);

    Sanctum::actingAs(User::factory()->gerenteSucursal($mia->id)->create());

    $this->getJson('/api/v1/sucursales')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.nombre', 'Mía');
});

test('no se elimina sucursal con pedidos activos 409', function () {
    Sanctum::actingAs(User::factory()->gerenteGeneral()->create());

    $s = Sucursal::factory()->create();
    Pedido::factory()->activo()->create(['sucursal_id' => $s->id]);

    $this->deleteJson("/api/v1/sucursales/{$s->id}")
        ->assertStatus(409);
});
