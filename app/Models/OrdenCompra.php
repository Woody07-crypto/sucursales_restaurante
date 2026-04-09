<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenCompra extends Model
{
    protected $table = 'ordenes_compra';

    protected $fillable = ['sucursal', 'ingrediente_id', 'tipo', 'cantidad_sugerida', 'estado', 'pedido_id'];

    protected function casts(): array
    {
        return [
            'cantidad_sugerida' => 'decimal:3',
        ];
    }

    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(Ingrediente::class, 'ingrediente_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
