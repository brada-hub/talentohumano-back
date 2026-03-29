<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // El orden importa debido a las llaves foráneas
        $this->call([
            GeoSeeder::class,           // 1. Catálogos Base (Ciudades, Departamentos)
            PersonalSeeder::class,      // 2. Depende de Geo (en el futuro personas lo hará)
            AuthSeeder::class,          // 3. Depende de Sistemas y Roles
            TalentoHumanoSeeder::class, // 4. Depende de Geo (para las sedes)
            BeneficiosSeeder::class,    // 5. Depende de Talento Humano
        ]);
    }
}
