<?php

use App\Models\AuditLog;
use App\Models\Pedido;
use App\Models\Product;
use App\Models\Sucursal;
use App\Models\User;

test('MAT-SUC-001 health sucursales sin token', function () {
    $this->getJson('/api/v1/sucursales/health')
        ->assertOk()
        ->assertJsonPath('flow', 'sucursales');
});

test('MAT-SUC-002 listar sucursales gerente global', function () {
    Sucursal::factory()->count(2)->create();
    $gg = User::factory()->gerenteGlobal()->create();

    $this->getJson('/api/v1/sucursales', apiBearer($gg))
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('MAT-SUC-003 listar sucursales gerente sucursal solo asignada', function () {
    $a = Sucursal::factory()->create();
    Sucursal::factory()->create();
    $gs = User::factory()->gerenteSucursal($a->id)->create();

    $json = $this->getJson('/api/v1/sucursales', apiBearer($gs))
        ->assertOk()
        ->json('data');

    expect($json)->toHaveCount(1)
        ->and($json[0]['id'])->toBe($a->id);
});

test('MAT-SUC-004 detalle con KPIs', function () {
    $s = Sucursal::factory()->create();
    Product::factory()->count(2)->create(['sucursal_id' => $s->id, 'activo' => true]);
    Pedido::factory()->forSucursal($s)->estado('pendiente')->create();
    Pedido::factory()->forSucursal($s)->estado('entregado')->create();

    $gg = User::factory()->gerenteGlobal()->create();

    $this->getJson("/api/v1/sucursales/{$s->id}", apiBearer($gg))
        ->assertOk()
        ->assertJsonPath('data.kpis.pedidos_activos', 1)
        ->assertJsonPath('data.kpis.pedidos_totales', 2)
        ->assertJsonPath('data.kpis.productos_activos', 2);
});

test('MAT-SUC-005 listar sin token responde 401', function () {
    $this->getJson('/api/v1/sucursales')->assertUnauthorized();
});

test('MAT-SUC-006 detalle sucursal inexistente 404', function () {
    $gg = User::factory()->gerenteGlobal()->create();

    $this->getJson('/api/v1/sucursales/999999', apiBearer($gg))
        ->assertNotFound();
});

test('MAT-SUC-007 crear sucursal 201 gerente global', function () {
    $gg = User::factory()->gerenteGlobal()->create();

    $this->postJson('/api/v1/sucursales', [
        'nombre' => 'Sucursal Centro',
        'direccion' => 'Av. Principal 1',
        'ciudad' => 'San Salvador',
        'telefono' => '7000-0000',
        'email' => 'centro@example.test',
        'horario' => '10:00–22:00',
        'activa' => true,
    ], apiBearer($gg))
        ->assertCreated()
        ->assertJsonPath('data.nombre', 'Sucursal Centro');
});

test('MAT-SUC-008 crear sucursal validación 422', function () {
    $gg = User::factory()->gerenteGlobal()->create();

    $this->postJson('/api/v1/sucursales', [
        'nombre' => 'Incompleta',
    ], apiBearer($gg))
        ->assertUnprocessable();
});

test('MAT-SUC-009 crear sucursal nombre duplicado 409', function () {
    Sucursal::factory()->create(['nombre' => 'Duplicada']);
    $gg = User::factory()->gerenteGlobal()->create();

    $this->postJson('/api/v1/sucursales', [
        'nombre' => 'Duplicada',
        'direccion' => 'Calle 2',
        'ciudad' => 'Ciudad',
        'telefono' => '1111',
    ], apiBearer($gg))
        ->assertStatus(409);
});

test('MAT-SUC-010 gerente sucursal no puede crear 403', function () {
    $s = Sucursal::factory()->create();
    $gs = User::factory()->gerenteSucursal($s->id)->create();

    $this->postJson('/api/v1/sucursales', [
        'nombre' => 'Nueva',
        'direccion' => 'X',
        'ciudad' => 'Y',
        'telefono' => '1',
    ], apiBearer($gs))
        ->assertForbidden();
});

test('MAT-SUC-011 actualizar sucursal 200', function () {
    $s = Sucursal::factory()->create(['ciudad' => 'Antigua']);
    $gs = User::factory()->gerenteSucursal($s->id)->create();

    $this->putJson("/api/v1/sucursales/{$s->id}", [
        'ciudad' => 'Nueva Ciudad',
    ], apiBearer($gs))
        ->assertOk()
        ->assertJsonPath('data.ciudad', 'Nueva Ciudad');
});

test('MAT-SUC-012 actualizar sucursal ajena GS 404', function () {
    $s1 = Sucursal::factory()->create();
    $s2 = Sucursal::factory()->create();
    $gs = User::factory()->gerenteSucursal($s1->id)->create();

    $this->putJson("/api/v1/sucursales/{$s2->id}", [
        'ciudad' => 'Hack',
    ], apiBearer($gs))
        ->assertNotFound();
});

test('MAT-SUC-013 auditoría en actualización', function () {
    $s = Sucursal::factory()->create();
    $gg = User::factory()->gerenteGlobal()->create();

    $this->putJson("/api/v1/sucursales/{$s->id}", [
        'horario' => '24h',
    ], apiBearer($gg))->assertOk();

    expect(AuditLog::query()->where('action', 'sucursal.updated')->count())->toBe(1);
});

test('MAT-SUC-014 eliminar sucursal con pedidos activos 409', function () {
    $s = Sucursal::factory()->create();
    Pedido::factory()->forSucursal($s)->estado('pendiente')->create();
    $gg = User::factory()->gerenteGlobal()->create();

    $this->deleteJson("/api/v1/sucursales/{$s->id}", [], apiBearer($gg))
        ->assertStatus(409);
});

test('MAT-SUC-015 eliminar sucursal soft delete y auditoría', function () {
    $s = Sucursal::factory()->create();
    $gg = User::factory()->gerenteGlobal()->create();

    $this->deleteJson("/api/v1/sucursales/{$s->id}", [], apiBearer($gg))
        ->assertOk()
        ->assertJsonPath('data.id', $s->id);

    $this->assertSoftDeleted('sucursales', ['id' => $s->id]);
    expect(AuditLog::query()->where('action', 'sucursal.soft_deleted')->count())->toBe(1);
});

test('MAT-SUC-016 gerente sucursal no elimina ajena 403', function () {
    $s1 = Sucursal::factory()->create();
    $s2 = Sucursal::factory()->create();
    $gs = User::factory()->gerenteSucursal($s1->id)->create();

    $this->deleteJson("/api/v1/sucursales/{$s2->id}", [], apiBearer($gs))
        ->assertForbidden();
});
