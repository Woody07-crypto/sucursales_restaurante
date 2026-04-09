<?php

namespace App\Http\Requests;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSucursalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $sucursal = $this->route('sucursal');

        if (! $user instanceof User || ! $sucursal instanceof Sucursal) {
            return false;
        }

        return $user->canAccessSucursal($sucursal);
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
            'manager_id' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
