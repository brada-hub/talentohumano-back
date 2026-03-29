<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Src\Geo\Infrastructure\Persistence\Models\PaisModel;
use Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel;
use Src\Geo\Infrastructure\Persistence\Models\CiudadModel;
use Src\Geo\Infrastructure\Persistence\Models\NacionalidadModel;

class GeoSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/geo_data.json');
        
        if (!File::exists($jsonPath)) {
            $this->command->error("Archivo de datos geo no encontrado en: $jsonPath");
            return;
        }

        $data = json_decode(File::get($jsonPath), true);
        
        $this->command->info("--- INICIANDO CARGA DE DATOS GEOGRÁFICOS ---");

        // 1. Desactivar checks de FK para limpieza rápida en PostgreSQL/MySQL
        // DB::statement('SET CONSTRAINTS ALL DEFERRED'); // PostgreSQL
        
        // 2. Poblado de Países y Nacionalidades
        $countryMap = [];
        foreach ($data['paises'] as $p) {
            $pais = PaisModel::updateOrCreate(
                ['iso2' => $p['iso2']],
                [
                    'nombre' => $p['nombre'],
                    'iso3' => $p['iso3'],
                    'prefijo_telefonico' => $p['prefijo'],
                    'activo' => true
                ]
            );
            $countryMap[$p['id']] = $pais->id_pais;

            NacionalidadModel::updateOrCreate(
                ['id_pais' => $pais->id_pais],
                ['gentilicio' => $p['gentilicio']]
            );
        }
        $this->command->info("✓ Países y Nacionalidades cargados.");

        // 3. Poblado de Departamentos
        $stateMap = [];
        foreach ($data['departamentos'] as $d) {
            $dbPaisId = $countryMap[$d['pais_id']] ?? null;
            if (!$dbPaisId) continue;

            $depto = DepartamentoModel::updateOrCreate(
                ['nombre' => $d['nombre'], 'pais_id' => $dbPaisId],
                ['codigo_expedido' => $d['codigo_expedido']]
            );
            $stateMap[$d['id']] = $depto->id_departamento;
        }
        $this->command->info("✓ Departamentos/Estados cargados.");

        // 4. Poblado de Ciudades en Bloques (Chunks)
        $this->command->info("✓ Cargando Ciudades en bloques de 1000...");
        
        // Limpiamos ciudades previas para evitar duplicidad si el usuario re-ejecuta
        DB::table('ciudades')->delete();

        $chunks = array_chunk($data['ciudades'], 1000);
        $totalCities = count($data['ciudades']);
        $count = 0;

        foreach ($chunks as $chunk) {
            $insertData = [];
            foreach ($chunk as $c) {
                $dbDeptoId = $stateMap[$c['departamento_id']] ?? null;
                if (!$dbDeptoId) continue;

                $insertData[] = [
                    'nombre' => $c['nombre'],
                    'departamento_id' => $dbDeptoId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count++;
            }
            
            DB::table('ciudades')->insert($insertData);
            $this->command->comment("Cargadas $count / $totalCities ciudades...");
        }

        $this->command->info("--- PROCESO COMPLETADO ---");
    }
}
