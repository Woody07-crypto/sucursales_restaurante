<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->words(2, true),
            'precio' => fake()->randomFloat(2, 5, 50),
            'sucursal_id' => Sucursal::factory(),
            'activo' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
