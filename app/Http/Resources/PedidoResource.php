<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PedidoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'cliente_nombre' => $this->cliente_nombre,
            'canal' => $this->canal,
            'sucursal' => $this->sucursal,
            'estado' => $this->estado,
            'total' => (float) $this->total,
            'items' => $this->items,
            'notas' => $this->notas,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
