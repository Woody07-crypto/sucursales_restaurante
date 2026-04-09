<?php

namespace Database\Seeders;

use App\Models\Ingrediente;
use App\Models\ProductoIngrediente;
use App\Models\StockSucursalIngrediente;
use Illuminate\Database\Seeder;

class StockFlujo3Seeder extends Seeder
{
    /**
     * Datos demo para el Flujograma 3 (receta de "Pizza" → ingredientes).
     */
    public function run(): void
    {
        $masa = Ingrediente::query()->updateOrCreate(
            ['slug' => 'masa'],
            ['nombre' => 'Masa', 'umbral' => 10]
        );

        $queso = Ingrediente::query()->updateOrCreate(
            ['slug' => 'queso'],
            ['nombre' => 'Queso', 'umbral' => 5]
        );

        ProductoIngrediente::query()->updateOrCreate(
            ['producto_nombre' => 'Pizza', 'ingrediente_id' => $masa->id],
            ['cantidad_por_unidad' => 3]
        );

        ProductoIngrediente::query()->updateOrCreate(
            ['producto_nombre' => 'Pizza', 'ingrediente_id' => $queso->id],
            ['cantidad_por_unidad' => 2]
        );

        foreach (['Santa Tecla', 'Centro'] as $sucursal) {
            StockSucursalIngrediente::query()->updateOrCreate(
                ['sucursal' => $sucursal, 'ingrediente_id' => $masa->id],
                ['cantidad' => 50]
            );
            StockSucursalIngrediente::query()->updateOrCreate(
                ['sucursal' => $sucursal, 'ingrediente_id' => $queso->id],
                ['cantidad' => 30]
            );
        }
    }
}
