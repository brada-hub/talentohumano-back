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
        $this->call([
            GeoSeeder::class,
            PersonalSeeder::class,
            TalentoHumanoSeeder::class,
            EstructuraInstitucionalSeeder::class,
            AuthSeeder::class,
            SigethPermissionsSeeder::class,
            BeneficiosSeeder::class,
        ]);
    }
}
