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

        // 2. Roles
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

        // 3. Permissions (Ejemplos)
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

            // Asignar al Admin
            DB::table('role_has_permissions')->insert([
                'role_id'       => $adminRolId,
                'permission_id' => $permisoId,
            ]);
        }

        // 4. Admin User (sin id_persona por ahora, se puede vincular después)
        $adminUser = UserModel::create([
            'username'   => 'admin',
            'password'   => Hash::make('admin123'),
            'activo'     => true,
            'id_persona' => null, // Opcional por ahora para pruebas iniciales
        ]);

        DB::table('user_has_roles')->insert([
            'user_id' => $adminUser->id_user,
            'role_id' => $adminRolId,
        ]);
    }
}
