<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'nombre'     => $this->nombre,
            'precio'     => $this->precio,
            'sucursal_id'=> $this->sucursal_id,
            'created_at' => $this->created_at,
        ];
    }
}