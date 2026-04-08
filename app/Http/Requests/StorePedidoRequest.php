<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_nombre' => ['nullable', 'string', 'max:120'],
            'canal' => ['required', 'string', 'in:salon,delivery,take-away'],
            'sucursal' => ['required', 'string', 'max:120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nombre' => ['required', 'string', 'max:120'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'notas' => ['nullable', 'string', 'max:500'],
        ];
    }
}
