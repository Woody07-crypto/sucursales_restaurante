<?php

namespace Database\Factories;

use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sucursal>
 */
class SucursalFactory extends Factory
{
    protected $model = Sucursal::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Sucursal '.fake()->unique()->lexify('????'),
            'direccion' => fake()->streetAddress(),
            'ciudad' => fake()->city(),
            'telefono' => fake()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'horario' => '09:00–22:00',
            'activa' => true,
            'manager_id' => null,
        ];
    }
}
