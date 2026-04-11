<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SigethPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $sigethId = DB::table('sistemas')->where('sistema', 'SIGETH')->value('id_sistema');
        if (!$sigethId) {
            return;
        }

        $adminRoleId = DB::table('roles')
            ->where('sistema_id', $sigethId)
            ->where('nombres', 'Administrador')
            ->value('id_rol');

        $permissions = [
            'personal.ver', 'personal.crear', 'personal.editar', 'personal.eliminar',
            'academico.ver', 'academico.crear', 'academico.editar', 'academico.eliminar',
            'beneficios.ver', 'beneficios.crear', 'beneficios.editar', 'beneficios.eliminar',
            'contrato.ver', 'contrato.crear', 'contrato.editar', 'contrato.finalizar', 'contrato.generar', 'contrato.firmar',
            'legajo.ver', 'legajo.subir', 'legajo.editar', 'legajo.eliminar',
            'recordatorios.ver', 'recordatorios.enviar',
            'reportes.ver', 'reportes.exportar',
            'estructura.ver', 'estructura.crear', 'estructura.editar', 'estructura.eliminar',
            'sedes.ver', 'sedes.crear', 'sedes.editar', 'sedes.eliminar',
            'geo.ver', 'geo.crear', 'geo.editar', 'geo.eliminar',
            'sso.ver', 'auth.usuarios.gestionar',
        ];

        foreach ($permissions as $permissionName) {
            $permissionId = DB::table('permissions')->where([
                'nombres' => $permissionName,
                'sistema_id' => $sigethId,
            ])->value('id_permision');

            if (!$permissionId) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'nombres' => $permissionName,
                    'sistema_id' => $sigethId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($adminRoleId) {
                DB::table('role_has_permissions')->updateOrInsert([
                    'role_id' => $adminRoleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }
}
