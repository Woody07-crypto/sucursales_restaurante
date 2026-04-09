<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    public function run(): void
    {
        Sucursal::query()->create([
            'nombre' => 'Casa central',
            'direccion' => 'Av. Principal 100',
            'ciudad' => 'Buenos Aires',
            'telefono' => '1144556677',
            'email' => 'central@restaurant.test',
            'horario' => 'Lun-Dom 10:00-23:00',
            'activa' => true,
        ]);

        Sucursal::query()->create([
            'nombre' => 'Sucursal Norte',
            'direccion' => 'Calle 50 Nº 200',
            'ciudad' => 'Buenos Aires',
            'telefono' => '1144332211',
            'email' => null,
            'horario' => null,
            'activa' => true,
        ]);
    }
}
