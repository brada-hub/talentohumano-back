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
        
        // 1. Sedes (IDs originales del servidor para que no se cruce la data)
        $sedesData = [
            ['id_sede' => 1, 'nombre' => 'LA PAZ', 'sigla' => 'LPZ', 'id_sede_padre' => null],
            ['id_sede' => 2, 'nombre' => 'EL ALTO', 'sigla' => 'EAL', 'id_sede_padre' => 1],
            ['id_sede' => 3, 'nombre' => 'COCHABAMBA', 'sigla' => 'COC', 'id_sede_padre' => null],
            ['id_sede' => 4, 'nombre' => 'IVIRGARZAMA', 'sigla' => 'IVI', 'id_sede_padre' => 3],
            ['id_sede' => 5, 'nombre' => 'GUAYARAMERIN', 'sigla' => 'GYA', 'id_sede_padre' => 8], // Guayará está en Beni/Pando region? ID 8 es Cobija
            ['id_sede' => 6, 'nombre' => 'SANTA CRUZ', 'sigla' => 'SCZ', 'id_sede_padre' => null],
            ['id_sede' => 7, 'nombre' => 'PUERTO QUIJARRO', 'sigla' => 'PQJ', 'id_sede_padre' => 6],
            ['id_sede' => 8, 'nombre' => 'COBIJA', 'sigla' => 'CBJ', 'id_sede_padre' => null],
            ['id_sede' => 9, 'nombre' => 'NACIONAL', 'sigla' => 'NAC', 'id_sede_padre' => null],
            
            // Nuevos Campus (IDs nuevos para no chocar con nada)
            ['id_sede' => 10, 'nombre' => 'CAMPUS FLORIDA', 'sigla' => 'FLO', 'id_sede_padre' => 3],
            ['id_sede' => 11, 'nombre' => 'CAMPUS JUAN PABLO', 'sigla' => 'JPA', 'id_sede_padre' => 3],
            ['id_sede' => 12, 'nombre' => 'CAMPUS COLONIAL', 'sigla' => 'COL', 'id_sede_padre' => 3],
        ];

        foreach ($sedesData as $sede) {
            SedeModel::updateOrCreate(['id_sede' => $sede['id_sede']], [
                'nombre' => $sede['nombre'],
                'sigla'  => $sede['sigla'],
                'id_sede_padre' => $sede['id_sede_padre'],
                'activo' => true
            ]);
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
