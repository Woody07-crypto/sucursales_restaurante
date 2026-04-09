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
            'canal' => ['required', 'string', 'max:80'],
            'sucursal' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            // Compatibilidad:
            // - Contrato de la matriz: items[] con product_id + cantidad
            // - Ejemplo del PDF: items[] con nombre + cantidad + precio_unitario
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.nombre' => ['nullable', 'string', 'max:255'],
            'items.*.precio_unitario' => ['nullable', 'numeric', 'min:0'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
            'cliente_nombre' => ['nullable', 'string', 'max:255'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
