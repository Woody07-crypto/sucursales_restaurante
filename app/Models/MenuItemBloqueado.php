<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemBloqueado extends Model
{
    protected $table = 'menu_items_bloqueados';

    protected $fillable = ['sucursal', 'producto_nombre', 'ingrediente_id', 'pedido_id'];

    public function ingrediente(): BelongsTo
    {
        return $this->belongsTo(Ingrediente::class, 'ingrediente_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
