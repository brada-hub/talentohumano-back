<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Beneficios\Infrastructure\Persistence\Models\ParentescoModel;

class BeneficiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentescos = [
            'Esposo(a)',
            'Hijo(a)',
            'Padre',
            'Madre',
        ];

        foreach ($parentescos as $name) {
            ParentescoModel::firstOrCreate(['nombre' => $name]);
        }
    }
}
