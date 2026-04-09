<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSucursalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof User && $this->user()->isGerenteGeneral();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['required', 'string', 'max:500'],
            'ciudad' => ['required', 'string', 'max:120'],
            'telefono' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'horario' => ['nullable', 'string', 'max:500'],
            'activa' => ['sometimes', 'boolean'],
            'manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
