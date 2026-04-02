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
            PersonalSeeder::class,      // 2. Depende de Geo
            TalentoHumanoSeeder::class, // 3. Catálogos de RRHH (Cajas, Pensiones, Sedes, Areas, Cargos)
            AuthSeeder::class,          // 4. Usuarios y Roles (Ahora usa datos de RRHH para el perfil admin)
            BeneficiosSeeder::class,    // 5. Depende de RRHH
        ]);
    }
}
