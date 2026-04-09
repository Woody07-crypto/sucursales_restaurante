<?php

namespace Database\Factories;

use App\Models\Pedido;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pedido>
 */
class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        return [
            'codigo' => 'PED-FCT-'.fake()->unique()->numerify('######'),
            'cliente_nombre' => fake()->optional()->name(),
            'canal' => fake()->randomElement(['salon', 'delivery', 'take-away']),
            'sucursal' => '',
            'sucursal_id' => Sucursal::factory(),
            'estado' => Pedido::ESTADO_PENDIENTE,
            'total' => fake()->randomFloat(2, 10, 500),
            'items' => [['nombre' => 'Item', 'cantidad' => 1, 'precio_unitario' => 10]],
            'notas' => null,
            'created_by' => null,
        ];
    }

    public function activo(): static
    {
        return $this->state(fn () => [
            'estado' => fake()->randomElement([
                Pedido::ESTADO_PENDIENTE,
                Pedido::ESTADO_EN_PREPARACION,
                Pedido::ESTADO_LISTO,
            ]),
        ]);
    }
}
