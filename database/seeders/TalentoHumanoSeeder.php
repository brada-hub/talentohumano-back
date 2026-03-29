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

        // Sedes
        SedeModel::firstOrCreate(['nombre' => 'Campus Central - Cochabamba'], ['id_ciudad' => 1]);
        SedeModel::firstOrCreate(['nombre' => 'Sub-sede - La Paz'], ['id_ciudad' => 2]);
        SedeModel::firstOrCreate(['nombre' => 'Sub-sede - Santa Cruz'], ['id_ciudad' => 3]);

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
