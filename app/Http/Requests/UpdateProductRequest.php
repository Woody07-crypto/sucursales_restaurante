<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'required', 'string', 'max:255'],
            'precio' => ['sometimes', 'required', 'numeric', 'min:0'],
            'sucursal_id' => ['sometimes', 'required', 'integer', Rule::exists('sucursales', 'id')],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}

