<?php

namespace App\Models;

use Database\Factories\PedidoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    /** @use HasFactory<PedidoFactory> */
    use HasFactory;

    protected $table = 'pedidos';

    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_EN_PREPARACION = 'en_preparacion';

    public const ESTADO_LISTO = 'listo';

    public const ESTADO_ENTREGADO = 'entregado';

    public const ESTADO_CANCELADO = 'cancelado';

    /** @var list<string> */
    public const ESTADOS_ACTIVOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_EN_PREPARACION,
        self::ESTADO_LISTO,
    ];

    protected $fillable = [
        'sucursal_id',
        'estado',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }
}
