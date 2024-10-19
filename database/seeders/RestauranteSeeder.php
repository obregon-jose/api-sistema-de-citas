<?php

namespace Database\Seeders;

use App\Models\Restaurante;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestauranteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Restaurante::create([
            'nombre' => 'Restaurante 1',
            'telefono' => '313291901',
            'direccion' => 'calle 123'
        ],
        [
            'nombre' => 'Restaurante 2',
            'telefono' => '313291901',
            'direccion' => 'calle 123'
        ],
        [
            'nombre' => 'Restaurante 3',
            'telefono' => '313291901',
            'direccion' => 'calle 123'
        ]);
    }
}
