<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoIngrediente extends Model
{
    protected $table = 'producto_ingrediente';

    protected $fillable = ['producto_nombre', 'ingrediente_id', 'cantidad_por_unidad'];

    protected function casts(): array
    {
        return [
            'cantidad_por_unidad' => 'decimal:3',
        ];
    }

    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(Ingrediente::class, 'ingrediente_id');
    }
}
