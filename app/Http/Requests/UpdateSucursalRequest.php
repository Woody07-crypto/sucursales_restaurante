<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSucursalRequest extends FormRequest
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
            'direccion' => ['sometimes', 'required', 'string', 'max:500'],
            'ciudad' => ['sometimes', 'required', 'string', 'max:120'],
            'telefono' => ['sometimes', 'required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'horario' => ['nullable', 'string', 'max:500'],
            'activa' => ['sometimes', 'boolean'],
        ];
    }
}
