<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingrediente extends Model
{
    protected $fillable = ['nombre', 'slug', 'umbral'];

    protected function casts(): array
    {
        return [
            'umbral' => 'decimal:3',
        ];
    }

    public function stocksSucursal(): HasMany
    {
        return $this->hasMany(StockSucursalIngrediente::class, 'ingrediente_id');
    }
}
