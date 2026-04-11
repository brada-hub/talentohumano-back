<?php

namespace Src\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Auth\Infrastructure\Persistence\Models\SistemaModel;
use Src\Auth\Infrastructure\Persistence\Models\RoleModel;
use Src\Auth\Infrastructure\Persistence\Models\PermissionModel;
use Src\Auth\Infrastructure\Persistence\Models\UserModel;
use Src\Shared\Infrastructure\Http\ApiResponse;

final class SsoManagementController extends Controller
{
    // --- Systems (Applications) ---
    public function getSystems(): JsonResponse
    {
        $systems = SistemaModel::withCount(['roles', 'permissions'])->get();
        return ApiResponse::success($systems);
    }

    public function storeSystem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sistema'     => 'required|string|max:255',
            'url_sistema' => 'required|url|max:255',
        ]);
        $system = SistemaModel::create($validated);
        return ApiResponse::success($system, 'Sistema creado correctamente');
    }

    public function updateSystem(Request $request, $id): JsonResponse
    {
        $system = SistemaModel::findOrFail($id);
        $validated = $request->validate([
            'sistema'     => 'sometimes|required|string|max:255',
            'url_sistema' => 'sometimes|required|url|max:255',
        ]);
        $system->update($validated);
        return ApiResponse::success($system, 'Sistema actualizado correctamente');
    }

    public function deleteSystem($id): JsonResponse
    {
        $system = SistemaModel::findOrFail($id);
        $system->delete();
        return ApiResponse::success(null, 'Sistema eliminado correctamente');
    }

    // --- Roles ---
    public function getRoles(Request $request): JsonResponse
    {
        $query = RoleModel::with(['sistema', 'permissions']);
        if ($request->has('sistema_id')) {
            $query->where('sistema_id', $request->sistema_id);
        }
        return ApiResponse::success($query->get());
    }

    public function storeRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombres'    => 'required|string|max:255',
            'sistema_id' => 'required|exists:sistemas,id_sistema',
            'permission_ids'   => 'array',
            'permission_ids.*' => 'exists:permissions,id_permision',
        ]);
        
        $role = RoleModel::create($validated);
        if (!empty($validated['permission_ids'])) {
            $role->permissions()->sync($validated['permission_ids']);
        }
        
        return ApiResponse::success($role->load(['sistema', 'permissions']), 'Rol creado correctamente');
    }

    public function updateRole(Request $request, $id): JsonResponse
    {
        $role = RoleModel::findOrFail($id);
        $validated = $request->validate([
            'nombres'    => 'sometimes|required|string|max:255',
            'sistema_id' => 'sometimes|required|exists:sistemas,id_sistema',
            'permission_ids'   => 'array',
            'permission_ids.*' => 'exists:permissions,id_permision',
        ]);
        
        $role->update($validated);
        if (isset($validated['permission_ids'])) {
            $role->permissions()->sync($validated['permission_ids']);
        }
        
        return ApiResponse::success($role->load(['sistema', 'permissions']), 'Rol actualizado correctamente');
    }

    // --- Permissions ---
    public function getPermissions(Request $request): JsonResponse
    {
        $query = PermissionModel::with('sistema');
        if ($request->has('sistema_id')) {
            $query->where('sistema_id', $request->sistema_id);
        }
        return ApiResponse::success($query->get());
    }

    public function storePermission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombres'    => 'required|string|max:255',
            'sistema_id' => 'required|exists:sistemas,id_sistema',
        ]);
        $permission = PermissionModel::create($validated);
        return ApiResponse::success($permission->load('sistema'), 'Permiso creado correctamente');
    }

    // --- User-Role Assignments ---
    public function getUsers(Request $request): JsonResponse
    {
        $users = UserModel::with(['roles.sistema', 'permissions.sistema'])->get();
        return ApiResponse::success($users);
    }

    public function updateAccess(Request $request, $userId): JsonResponse
    {
        $user = UserModel::findOrFail($userId);
        $validated = $request->validate([
            'role_ids'         => 'array',
            'role_ids.*'       => 'exists:roles,id_rol',
            'permission_ids'   => 'array',
            'permission_ids.*' => 'exists:permissions,id_permision',
            'id_sede_scope'    => 'nullable|exists:sedes,id_sede',
        ]);
        
        $user->roles()->sync($validated['role_ids'] ?? []);
        $user->permissions()->sync($validated['permission_ids'] ?? []);
        $user->id_sede_scope = $validated['id_sede_scope'] ?? null;
        $user->save();
        
        return ApiResponse::success($user->load(['roles.sistema', 'permissions.sistema']), 'Accesos actualizados correctamente');
    }

    public function updateUserStatus($id): JsonResponse
    {
        $user = UserModel::findOrFail($id);
        $user->activo = !$user->activo;
        $user->save();
        return ApiResponse::success($user, 'Estado de usuario actualizado');
    }

    public function resetUserPassword($id): JsonResponse
    {
        // We use the relationship defined in UserModel (assumed via id_persona)
        $user = UserModel::findOrFail($id);
        
        // Find persona CI
        $persona = \Src\Personal\Infrastructure\Persistence\Models\PersonaModel::find($user->id_persona);
        $newPassword = $persona ? $persona->ci : $user->username;
        
        $user->password = \Illuminate\Support\Facades\Hash::make($newPassword);
        $user->debe_cambiar_password = true;
        $user->save();
        
        return ApiResponse::success(null, 'Contraseña reiniciada correctamente (CI por defecto).');
    }

    public function getPersonasWithoutUser(): JsonResponse
    {
        $personaIdsInUsers = UserModel::whereNotNull('id_persona')->pluck('id_persona')->toArray();
        $personas = \Src\Personal\Infrastructure\Persistence\Models\PersonaModel::whereNotIn('id', $personaIdsInUsers)
            ->select('id', 'nombres', 'primer_apellido', 'segundo_apellido', 'ci')
            ->get();
        return ApiResponse::success($personas);
    }

    public function storeUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username'   => 'required|unique:users,username',
            'password'   => 'required|min:4',
            'id_persona' => 'required|exists:personas,id',
            'role_ids'   => 'array',
            'role_ids.*' => 'exists:roles,id_rol',
        ]);
        
        $user = UserModel::create([
            'username'   => $validated['username'],
            'password'   => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'id_persona' => $validated['id_persona'],
            'activo'     => true,
            'debe_cambiar_password' => true,
        ]);
        
        if (!empty($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }
        
        return ApiResponse::success($user->load('roles.sistema'), 'Usuario creado correctamente');
    }
}
