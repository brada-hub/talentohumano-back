<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Src\Auth\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Support\Facades\DB;

class AuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Sistemas
        $sigethId = DB::table('sistemas')->insertGetId([
            'sistema'     => 'SIGETH',
            'url_sistema' => 'http://localhost:9000',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $sispoId = DB::table('sistemas')->insertGetId([
            'sistema'     => 'SISPO',
            'url_sistema' => 'http://localhost:9001',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $sigvaId = DB::table('sistemas')->insertGetId([
            'sistema'     => 'SIGVA',
            'url_sistema' => 'http://localhost:9002',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // 2. Roles (SIGETH)
        $adminRolId = DB::table('roles')->insertGetId([
            'nombres'    => 'Administrador',
            'sistema_id' => $sigethId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $directorRolId = DB::table('roles')->insertGetId([
            'nombres'    => 'Director (Encargado)',
            'sistema_id' => $sigethId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2.1 Roles (SISPO)
        $sispoAdminRolId = DB::table('roles')->insertGetId([
            'nombres'    => 'Administrador',
            'sistema_id' => $sispoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sispoEvaluadorRolId = DB::table('roles')->insertGetId([
            'nombres'    => 'Evaluador',
            'sistema_id' => $sispoId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2.2 Roles (SIGVA)
        $sigvaAdminRolId = DB::table('roles')->insertGetId([
            'nombres'    => 'Administrador',
            'sistema_id' => $sigvaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sigvaRrhhRolId = DB::table('roles')->insertGetId([
            'nombres'    => 'RRHH',
            'sistema_id' => $sigvaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Permissions (SIGETH)
        $permisosDesc = [
            'personal.ver', 'personal.crear', 'personal.editar', 'personal.eliminar',
            'empleado.ver', 'empleado.crear', 'empleado.editar',
            'contrato.ver', 'contrato.crear', 'contrato.editar',
            'auth.usuarios.gestionar',
        ];

        foreach ($permisosDesc as $permiso) {
            $permisoId = DB::table('permissions')->insertGetId([
                'nombres'    => $permiso,
                'sistema_id' => $sigethId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('role_has_permissions')->insert([
                'role_id'       => $adminRolId,
                'permission_id' => $permisoId,
            ]);
        }

        // 3.1 Permissions (SISPO)
        $sispoPermisos = ['dashboard', 'convocatorias', 'postulaciones', 'evaluaciones', 'sedes', 'cargos', 'requisitos', 'usuarios', 'roles'];
        foreach ($sispoPermisos as $permiso) {
            $permisoId = DB::table('permissions')->insertGetId([
                'nombres'    => $permiso,
                'sistema_id' => $sispoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('role_has_permissions')->insert([
                'role_id'       => $sispoAdminRolId,
                'permission_id' => $permisoId,
            ]);
        }

        // 3.2 Permissions (SIGVA)
        $sigvaPermisos = ['vacaciones_dashboard', 'solicitudes', 'calendario', 'empleados', 'feriados', 'reportes', 'documentacion'];
        foreach ($sigvaPermisos as $permiso) {
            $permisoId = DB::table('permissions')->insertGetId([
                'nombres'    => $permiso,
                'sistema_id' => $sigvaId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('role_has_permissions')->insert([
                'role_id'       => $sigvaAdminRolId,
                'permission_id' => $permisoId,
            ]);
        }

        // 4. Admin User (Brayan David Padilla - 13260003)
        // If the user already registered in the frontend, we use that persona, otherwise we create a basic one.
        $persona = \Src\Personal\Infrastructure\Persistence\Models\PersonaModel::firstOrCreate(
            ['ci' => '13260003'],
            [
                'id'                  => (string) \Illuminate\Support\Str::uuid(),
                'nombres'             => 'BRAYAN DAVID',
                'primer_apellido'     => 'PADILLA',
                'segundo_apellido'    => 'SILES',
                'id_ci_expedido'      => 1, // Default CBBA
                'id_sexo'             => 1, // Masculino
                'celular_personal'    => '67544099',
                'correo_personal'     => 'padillasilesbrayandavid@gmail.com',
                'estado_civil'        => 'Soltero',
                'id_nacionalidad'     => 1, // Boliviana
                'direccion_domicilio' => 'Dirección Registrada',
                'id_ciudad'           => 1,
                'id_pais'             => 1,
                'activo'              => true,
            ]
        );

        // Convert the person into an Employee so they appear in the Dashboard
        $empleado = \Src\TalentoHumano\Infrastructure\Persistence\Models\EmpleadoModel::firstOrCreate(
            ['id_persona' => $persona->id],
            [
                'celular_institucional' => '',
                'correo_institucional'  => 'brayanpadilla@unitepc.edu',
                'id_caja'               => null,
                'id_entidad_pensiones'  => null,
                'estado_laboral'        => 'Activo',
            ]
        );

        // Give them a contract so they count as "Personal Activo"
        \Src\TalentoHumano\Infrastructure\Persistence\Models\ContratoModel::firstOrCreate(
            ['id_empleado' => $empleado->id_empleado],
            [
                'id_tipo_contrato' => 1, // Planta
                'id_area'          => 3, // Talento Humano
                'id_cargo'         => 3, // Jefe de Talento Humano
                'salario'          => 12000.00,
                'fecha_inicio'     => '2024-01-01',
                'id_sede'          => 1, // Campus Central
                'estado_contrato'  => 'Activo',
            ]
        );

        // Create or update the Admin User and link to the Persona
        $adminUser = UserModel::updateOrCreate(
            ['username' => '13260003'],
            [
                'password'   => Hash::make('13260003'),
                'activo'     => true,
                'id_persona' => $persona->id,
            ]
        );

        // Ensure Admin Roles are attached without duplicates
        DB::table('user_has_roles')->updateOrInsert(
            ['user_id' => $adminUser->id_user, 'role_id' => $adminRolId]
        );
        DB::table('user_has_roles')->updateOrInsert(
            ['user_id' => $adminUser->id_user, 'role_id' => $sispoAdminRolId]
        );
        DB::table('user_has_roles')->updateOrInsert(
            ['user_id' => $adminUser->id_user, 'role_id' => $sigvaAdminRolId]
        );
    }
}
