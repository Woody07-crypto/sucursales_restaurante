<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSucursalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'nombre' => ['sometimes', 'string', 'max:255', Rule::unique('sucursales', 'nombre')->ignore($id)],
            'direccion' => ['sometimes', 'string', 'max:500'],
            'ciudad' => ['sometimes', 'string', 'max:120'],
            'telefono' => ['sometimes', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'horario' => ['nullable', 'string', 'max:255'],
            'activa' => ['sometimes', 'boolean'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
