<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sucursal extends Model
{
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

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function activePedidos(): HasMany
    {
        return $this->pedidos()->whereNotIn('estado', ['entregado', 'cancelado']);
    }
}
