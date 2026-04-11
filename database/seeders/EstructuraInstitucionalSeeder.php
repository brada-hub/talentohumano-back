<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\TalentoHumano\Infrastructure\Persistence\Models\AreaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CargoFuncionModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CargoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\GrupoPersonalModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\NivelJerarquicoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\PuestoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\SedeModel;

class EstructuraInstitucionalSeeder extends Seeder
{
    public function run(): void
    {
        $grupos = [
            ['nombre' => 'Administrativo', 'descripcion' => 'Personal administrativo de la institucion'],
            ['nombre' => 'Docente', 'descripcion' => 'Planta docente y academica'],
            ['nombre' => 'Directorio', 'descripcion' => 'Directorio institucional'],
            ['nombre' => 'Autoridades', 'descripcion' => 'Autoridades academicas y administrativas'],
            ['nombre' => 'Jefes de Carrera', 'descripcion' => 'Responsables de carrera y coordinacion academica'],
        ];

        foreach ($grupos as $grupo) {
            GrupoPersonalModel::updateOrCreate(
                ['nombre' => $grupo['nombre']],
                ['descripcion' => $grupo['descripcion'], 'activo' => true]
            );
        }

        $areas = [
            ['nombre_area' => 'Talento Humano', 'tipo_area' => 'Administrativa'],
            ['nombre_area' => 'Contabilidad', 'tipo_area' => 'Administrativa'],
            ['nombre_area' => 'Sistemas', 'tipo_area' => 'Administrativa'],
            ['nombre_area' => 'Registros', 'tipo_area' => 'Administrativa'],
            ['nombre_area' => 'Rectorado', 'tipo_area' => 'Direccion'],
            ['nombre_area' => 'Vicerrectorado', 'tipo_area' => 'Direccion'],
        ];

        foreach ($areas as $area) {
            AreaModel::updateOrCreate(
                ['nombre_area' => $area['nombre_area']],
                ['tipo_area' => $area['tipo_area'], 'activo' => true]
            );
        }

        $mandoMedio = NivelJerarquicoModel::firstOrCreate(
            ['nombre' => 'Mando Medio'],
            ['descripcion' => 'Jefaturas y direcciones', 'activo' => true]
        );

        $operativo = NivelJerarquicoModel::firstOrCreate(
            ['nombre' => 'Operativo'],
            ['descripcion' => 'Personal operativo y de apoyo', 'activo' => true]
        );

        $cargos = [
            [
                'nombre_cargo' => 'Jefe de Reclutamiento, Seleccion, Contratacion y Gestion de Talento Humano',
                'descripcion' => 'Responsable de reclutamiento, seleccion, contratacion y gestion integral del talento humano.',
                'id_nivel_jerarquico' => $mandoMedio->id_jerarquico,
                'funciones' => [
                    'Planificar y ejecutar procesos de reclutamiento y seleccion.',
                    'Coordinar contrataciones, ingresos y documentacion laboral.',
                    'Gestionar indicadores y seguimiento de talento humano.',
                    'Supervisar cumplimiento de politicas internas del area.',
                ],
            ],
            [
                'nombre_cargo' => 'Jefe de Gestion de Seguridad Social y Desvinculacion',
                'descripcion' => 'Responsable de seguridad social, desvinculacion y cumplimiento de obligaciones laborales.',
                'id_nivel_jerarquico' => $mandoMedio->id_jerarquico,
                'funciones' => [
                    'Administrar afiliaciones y bajas en cajas y AFP.',
                    'Gestionar desvinculaciones y cierres documentales.',
                    'Controlar obligaciones de seguridad social del personal.',
                    'Coordinar reportes de cumplimiento laboral y previsional.',
                ],
            ],
            [
                'nombre_cargo' => 'Jefe de Gestion de Planillas y Archivo',
                'descripcion' => 'Responsable de planillas, archivo documental y control de expedientes.',
                'id_nivel_jerarquico' => $mandoMedio->id_jerarquico,
                'funciones' => [
                    'Supervisar elaboracion y control de planillas.',
                    'Gestionar archivo fisico y digital del personal.',
                    'Controlar integridad del legajo documental.',
                    'Coordinar actualizacion periodica de expedientes.',
                ],
            ],
            [
                'nombre_cargo' => 'Auxiliar de Talento Humano',
                'descripcion' => 'Apoyo operativo y administrativo al area de talento humano.',
                'id_nivel_jerarquico' => $operativo->id_jerarquico,
                'funciones' => [
                    'Apoyar en la organizacion de expedientes del personal.',
                    'Registrar y actualizar informacion administrativa.',
                    'Dar soporte en tramites y seguimiento documental.',
                    'Apoyar en actividades operativas del area.',
                ],
            ],
        ];

        foreach ($cargos as $cargoData) {
            $cargo = CargoModel::updateOrCreate(
                ['nombre_cargo' => $cargoData['nombre_cargo']],
                [
                    'descripcion' => $cargoData['descripcion'],
                    'id_nivel_jerarquico' => $cargoData['id_nivel_jerarquico'],
                    'activo' => true,
                ]
            );

            CargoFuncionModel::where('id_cargo', $cargo->id_cargo)->delete();

            foreach ($cargoData['funciones'] as $index => $funcion) {
                CargoFuncionModel::create([
                    'id_cargo' => $cargo->id_cargo,
                    'descripcion' => $funcion,
                    'orden' => $index + 1,
                    'activo' => true,
                ]);
            }
        }

        $grupoAdministrativo = GrupoPersonalModel::where('nombre', 'Administrativo')->first();
        $areaTalentoHumano = AreaModel::where('nombre_area', 'Talento Humano')->first();
        $sedeCochabamba = SedeModel::where('nombre', 'COCHABAMBA')->first();

        $puestos = [
            [
                'codigo' => 'TH-REC-001',
                'nombre_puesto' => 'Jefe de Reclutamiento y Seleccion - Talento Humano',
                'cargo' => 'Jefe de Reclutamiento, Seleccion, Contratacion y Gestion de Talento Humano',
                'plantilla_contractual' => 'indefinido',
            ],
            [
                'codigo' => 'TH-SEG-001',
                'nombre_puesto' => 'Jefe de Seguridad Social y Desvinculacion - Talento Humano',
                'cargo' => 'Jefe de Gestion de Seguridad Social y Desvinculacion',
                'plantilla_contractual' => 'indefinido',
            ],
            [
                'codigo' => 'TH-PLA-001',
                'nombre_puesto' => 'Jefe de Planillas y Archivo - Talento Humano',
                'cargo' => 'Jefe de Gestion de Planillas y Archivo',
                'plantilla_contractual' => 'indefinido',
            ],
            [
                'codigo' => 'TH-AUX-001',
                'nombre_puesto' => 'Auxiliar de Talento Humano - Talento Humano',
                'cargo' => 'Auxiliar de Talento Humano',
                'plantilla_contractual' => 'indefinido',
            ],
        ];

        foreach ($puestos as $puestoData) {
            $cargo = CargoModel::where('nombre_cargo', $puestoData['cargo'])->first();

            if (!$cargo || !$grupoAdministrativo || !$areaTalentoHumano) {
                continue;
            }

            PuestoModel::updateOrCreate(
                ['codigo' => $puestoData['codigo']],
                [
                    'nombre_puesto' => $puestoData['nombre_puesto'],
                    'id_area' => $areaTalentoHumano->id_area,
                    'id_cargo' => $cargo->id_cargo,
                    'id_grupo_personal' => $grupoAdministrativo->id_grupo_personal,
                    'id_sede' => $sedeCochabamba?->id_sede,
                    'plantilla_contractual' => $puestoData['plantilla_contractual'],
                    'descripcion' => 'Puesto institucional del area de Talento Humano.',
                    'activo' => true,
                ]
            );
        }
    }
}
