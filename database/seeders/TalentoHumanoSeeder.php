<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CajaSaludModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EntidadPensionesModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\TipoContratoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\NivelJerarquicoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\AreaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\SedeModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CargoModel;

class TalentoHumanoSeeder extends Seeder
{
    public function run(): void
    {
        // Cajas de Salud
        CajaSaludModel::firstOrCreate(['nombre' => 'Caja Nacional de Salud (CNS)']);
        CajaSaludModel::firstOrCreate(['nombre' => 'Caja de Salud de la Banca Privada']);
        CajaSaludModel::firstOrCreate(['nombre' => 'Caja Petrolera de Salud']);
        CajaSaludModel::firstOrCreate(['nombre' => 'Otra']);

        // Pensiones
        EntidadPensionesModel::firstOrCreate(['nombre' => 'AFP Previsión (Gestora Pública)']);
        EntidadPensionesModel::firstOrCreate(['nombre' => 'AFP Futuro (Gestora Pública)']);

        // Tipos de Contrato (solo 3)
        TipoContratoModel::firstOrCreate(['nombre' => 'Contrato Indefinido']);
        TipoContratoModel::firstOrCreate(['nombre' => 'Plazo Fijo']);
        TipoContratoModel::firstOrCreate(['nombre' => 'Consultor']);

        // Niveles Jerárquicos
        NivelJerarquicoModel::firstOrCreate(
            ['nombre' => 'Mando Superior'],
            ['descripcion' => 'Rectorado y Decanato']
        );
        NivelJerarquicoModel::firstOrCreate(
            ['nombre' => 'Mando Medio'],
            ['descripcion' => 'Jefaturas de Departamento y Direcciones']
        );
        NivelJerarquicoModel::firstOrCreate(
            ['nombre' => 'Operativo'],
            ['descripcion' => 'Personal Administrativo y de Apoyo']
        );
        NivelJerarquicoModel::firstOrCreate(
            ['nombre' => 'Académico'],
            ['descripcion' => 'Planta Docente']
        );
        
        // 1. Sedes (LAS 9 PRINCIPALES SEGÚN SOLICITUD)
        $sedesData = [
            ['id_sede' => 1, 'nombre' => 'LA PAZ',        'sigla' => 'LPZ', 'id_departamento' => 28],
            ['id_sede' => 2, 'nombre' => 'EL ALTO',       'sigla' => 'EAL', 'id_departamento' => 28],
            ['id_sede' => 3, 'nombre' => 'COCHABAMBA',    'sigla' => 'COC', 'id_departamento' => 27],
            ['id_sede' => 4, 'nombre' => 'IVIRGARZAMA',    'sigla' => 'IVI', 'id_departamento' => 27],
            ['id_sede' => 5, 'nombre' => 'GUAYARAMERIN',  'sigla' => 'GYA', 'id_departamento' => 25],
            ['id_sede' => 6, 'nombre' => 'SANTA CRUZ',    'sigla' => 'SCZ', 'id_departamento' => 32],
            ['id_sede' => 7, 'nombre' => 'PUERTO QUIJARRO', 'sigla' => 'PQJ', 'id_departamento' => 32],
            ['id_sede' => 8, 'nombre' => 'COBIJA',        'sigla' => 'CBJ', 'id_departamento' => 30],
            ['id_sede' => 9, 'nombre' => 'NACIONAL',      'sigla' => 'NAC', 'id_departamento' => null],
        ];

        foreach ($sedesData as $sede) {
            SedeModel::updateOrCreate(['id_sede' => $sede['id_sede']], [
                'nombre'          => $sede['nombre'],
                'sigla'           => $sede['sigla'],
                'id_departamento' => $sede['id_departamento'],
                'activo'          => true
            ]);
        }

        // 2. Campus (Dependen de las Sedes)
        $campusData = [
            ['nombre' => 'CAMPUS FLORIDA',  'sigla' => 'FLO', 'id_sede' => 3],
            ['nombre' => 'CAMPUS JUAN PABLO', 'sigla' => 'JPA', 'id_sede' => 3],
            ['nombre' => 'CAMPUS COLONIAL',  'sigla' => 'COL', 'id_sede' => 3],
            ['nombre' => 'CAMPUS SANTA CRUZ', 'sigla' => 'SC1', 'id_sede' => 6],
            ['nombre' => 'CAMPUS LA PAZ',    'sigla' => 'LP1', 'id_sede' => 1],
        ];

        foreach ($campusData as $campus) {
             \Src\TalentoHumano\Infrastructure\Persistence\Models\CampusModel::updateOrCreate(
                ['nombre' => $campus['nombre']],
                ['sigla' => $campus['sigla'], 'id_sede' => $campus['id_sede'], 'activo' => true]
            );
        }

        // Areas
        $rectorado = AreaModel::firstOrCreate(['nombre_area' => 'Rectorado'], ['tipo_area' => 'Administrativa']);
        $facultadTecno = AreaModel::firstOrCreate(
            ['nombre_area' => 'Facultad de Tecnología'],
            ['tipo_area' => 'Académica', 'id_area_padre' => $rectorado->id_area]
        );
        AreaModel::firstOrCreate(
            ['nombre_area' => 'Talento Humano'],
            ['tipo_area' => 'Administrativa', 'id_area_padre' => $rectorado->id_area]
        );
        AreaModel::firstOrCreate(
            ['nombre_area' => 'Carrera de Ingeniería de Sistemas'],
            ['tipo_area' => 'Académica', 'id_area_padre' => $facultadTecno->id_area]
        );

        // Cargos
        CargoModel::firstOrCreate(['nombre_cargo' => 'Docente a Tiempo Completo'], ['id_nivel_jerarquico' => 4]);
        CargoModel::firstOrCreate(['nombre_cargo' => 'Director de Carrera'], ['id_nivel_jerarquico' => 2]);
        CargoModel::firstOrCreate(['nombre_cargo' => 'Jefe de Talento Humano'], ['id_nivel_jerarquico' => 2]);
        CargoModel::firstOrCreate(['nombre_cargo' => 'Auxiliar Administrativo'], ['id_nivel_jerarquico' => 3]);
    }
}
