<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'codigo',
        'cliente_nombre',
        'canal',
        'sucursal',
        'estado',
        'total',
        'items',
        'notas',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'total' => 'decimal:2',
        ];
    }
}
