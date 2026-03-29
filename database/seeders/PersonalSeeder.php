<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Personal\Infrastructure\Persistence\Models\SexoModel;
use Src\Personal\Infrastructure\Persistence\Models\GradoAcademicoModel;

class PersonalSeeder extends Seeder
{
    public function run(): void
    {
        // Sexo
        SexoModel::firstOrCreate(['sexo' => 'Masculino']);
        SexoModel::firstOrCreate(['sexo' => 'Femenino']);

        // Grado Académico
        $grados = [
            ['nombre' => 'Primaria', 'descripcion' => 'Educación básica'],
            ['nombre' => 'Secundaria', 'descripcion' => 'Educación media'],
            ['nombre' => 'Técnico Medio', 'descripcion' => 'Formación técnica'],
            ['nombre' => 'Técnico Superior', 'descripcion' => 'Formación técnica avanzada'],
            ['nombre' => 'Licenciatura', 'descripcion' => 'Grado universitario'],
            ['nombre' => 'Maestría', 'descripcion' => 'Postgrado'],
            ['nombre' => 'Doctorado', 'descripcion' => 'Grado máximo'],
        ];

        foreach ($grados as $grado) {
            GradoAcademicoModel::firstOrCreate(['nombre' => $grado['nombre']], $grado);
        }
    }
}
