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
            'Hijo(a)', 'Cónyuge', 'Padre', 'Madre', 'Hermano(a)', 'Nieto(a)', 'Abuelo(a)',
            'Tío(a)', 'Sobrino(a)', 'Otro'
        ];

        foreach ($parentescos as $name) {
            ParentescoModel::create(['nombre' => $name]);
        }
    }
}
