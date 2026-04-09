<?php

namespace Database\Factories;

use App\Models\Pedido;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pedido>
 */
class PedidoFactory extends Factory
{
    protected $model = Pedido::class;

    public function definition(): array
    {
        return [
            'sucursal_id' => Sucursal::factory(),
            'codigo' => strtoupper(Str::random(10)),
            'cliente_nombre' => fake()->name(),
            'canal' => 'mostrador',
            'sucursal' => '—',
            'estado' => 'pendiente',
            'total' => 10.5,
            'items' => [['product_id' => 1, 'cantidad' => 1, 'precio_unitario' => 10.5]],
            'notas' => null,
            'created_by' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Pedido $pedido): void {
            $nombre = Sucursal::query()->whereKey($pedido->sucursal_id)->value('nombre');
            if ($nombre && $pedido->sucursal === '—') {
                $pedido->update(['sucursal' => $nombre]);
            }
        });
    }

    public function forSucursal(Sucursal $sucursal): static
    {
        return $this->state(fn (array $attributes) => [
            'sucursal_id' => $sucursal->id,
            'sucursal' => $sucursal->nombre,
        ]);
    }

    public function estado(string $estado): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => $estado,
        ]);
    }

    public function byUser(?User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user?->id,
        ]);
    }
}
