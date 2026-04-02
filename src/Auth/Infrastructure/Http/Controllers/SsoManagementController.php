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
        $users = UserModel::with(['roles.sistema'])->get();
        return ApiResponse::success($users);
    }

    public function assignRolesToUser(Request $request, $userId): JsonResponse
    {
        $user = UserModel::findOrFail($userId);
        $validated = $request->validate([
            'role_ids'   => 'required|array',
            'role_ids.*' => 'exists:roles,id_rol',
        ]);
        
        $user->roles()->sync($validated['role_ids']);
        return ApiResponse::success($user->load('roles.sistema'), 'Roles asignados correctamente');
    }
}
