<?php

namespace App\Models;

use Database\Factories\SucursalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sucursal extends Model
{
    /** @use HasFactory<SucursalFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion',
        'ciudad',
        'telefono',
        'email',
        'horario',
        'activa',
        'manager_id',
    ];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
        ];
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function hasPedidosActivos(): bool
    {
        return $this->pedidos()
            ->whereIn('estado', Pedido::ESTADOS_ACTIVOS)
            ->exists();
    }
}
