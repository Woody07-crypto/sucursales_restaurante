<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['nombre', 'precio', 'sucursal_id'];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}