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
        'codigo',
        'cliente_nombre',
        'canal',
        'sucursal',
        'sucursal_id',
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

    protected static function booted(): void
    {
        static::creating(function (Pedido $pedido): void {
            if ($pedido->sucursal_id && blank($pedido->sucursal)) {
                $nombre = Sucursal::query()->whereKey($pedido->sucursal_id)->value('nombre');
                if ($nombre) {
                    $pedido->sucursal = $nombre;
                }
            }
        });
    }

    public function sucursalModel(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
