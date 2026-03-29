<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Personal\Infrastructure\Persistence\Models\PersonaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EmpleadoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ContratoModel;
use Illuminate\Support\Str;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. CARLOS ORTEGA - Docente (ID 1 es Masculino, ID 25 es Licenciatura, ID 1 es Nacionalidad Boliviana)
        $carlos = PersonaModel::create([
            'primer_apellido'     => 'Ortega',
            'segundo_apellido'    => 'Pinto',
            'nombres'             => 'Carlos Alberto',
            'ci'                  => '1234567',
            'complemento'         => null,
            'id_ci_expedido'      => 1, // CB
            'id_sexo'             => 1, // Masculino
            'celular_personal'    => '70712345',
            'correo_personal'     => 'carlos@unitepc.edu',
            'estado_civil'        => 'Casado',
            'id_nacionalidad'     => 1, // Boliviana
            'direccion_domicilio' => 'Av. Villazon Km 5',
            'id_ciudad'           => 1, // Cochabamba
            'id_pais'             => 1, // Bolivia
            'activo'              => true,
        ]);

        $empCarlos = EmpleadoModel::create([
            'id_persona'            => $carlos->id,
            'celular_institucional' => '70000001',
            'correo_institucional'  => 'c.ortega@unitepc.edu',
            'id_caja'               => 1, // CNS
            'id_entidad_pensiones'  => 1, // AFP Prevision
            'estado_laboral'        => 'Activo',
        ]);

        ContratoModel::create([
            'id_empleado'      => $empCarlos->id_empleado,
            'id_tipo_contrato' => 1, // Planta
            'id_area'          => 4, // Ing. Sistemas
            'id_cargo'         => 1, // Docente Tiempo Completo
            'salario'          => 5500.00,
            'fecha_inicio'     => '2025-01-01',
            'id_sede'          => 1, // Campus Central
            'estado_contrato'  => 'Activo',
        ]);

        // 2. ANA MARIA LOPEZ - Administradora
        $ana = PersonaModel::create([
            'primer_apellido'     => 'Lopez',
            'segundo_apellido'    => 'Castro',
            'nombres'             => 'Ana Maria',
            'ci'                  => '7654321',
            'id_ci_expedido'      => 2, // LP
            'id_sexo'             => 2, // Femenino
            'celular_personal'    => '60611223',
            'correo_personal'     => 'ana@gmail.com',
            'estado_civil'        => 'Soltera',
            'id_nacionalidad'     => 1,
            'direccion_domicilio' => 'C. Lanza #456',
            'id_ciudad'           => 1,
            'id_pais'             => 1,
        ]);

        $empAna = EmpleadoModel::create([
            'id_persona' => $ana->id,
            'id_caja'    => 2,
            'id_entidad_pensiones' => 2,
            'estado_laboral' => 'Activo',
        ]);

        ContratoModel::create([
            'id_empleado'      => $empAna->id_empleado,
            'id_tipo_contrato' => 1,
            'id_area'          => 3, // Talento Humano
            'id_cargo'         => 3, // Jefe TH
            'salario'          => 8000.00,
            'fecha_inicio'     => '2024-06-01',
            'id_sede'          => 1,
            'estado_contrato'  => 'Activo',
        ]);
    }
}
