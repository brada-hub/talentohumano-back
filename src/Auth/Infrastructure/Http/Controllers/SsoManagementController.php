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
        $users = UserModel::with(['roles.sistema', 'permissions.sistema', 'persona', 'sede'])->get();
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
        
        return ApiResponse::success($user->load(['roles.sistema', 'permissions.sistema', 'persona', 'sede']), 'Accesos actualizados correctamente');
    }

    public function updateUserStatus($id): JsonResponse
    {
        $user = UserModel::findOrFail($id);
        $user->activo = !$user->activo;
        $user->save();
        return ApiResponse::success($user->load(['roles.sistema', 'permissions.sistema', 'persona', 'sede']), 'Estado de usuario actualizado');
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
            'id_persona'       => 'nullable|exists:personas,id',
            'nombres'          => 'required_without:id_persona|nullable|string|max:255',
            'primer_apellido'  => 'required_without:id_persona|nullable|string|max:255',
            'segundo_apellido' => 'nullable|string|max:255',
            'ci'               => 'required_without:id_persona|nullable|string|max:50',
            'username'         => 'nullable|string|max:255',
            'password'         => 'nullable|min:4',
            'id_sede_scope'    => 'nullable|exists:sedes,id_sede',
            'role_ids'         => 'array',
            'role_ids.*'       => 'exists:roles,id_rol',
        ]);

        $idPersona = $validated['id_persona'] ?? null;

        // If id_persona not provided, find or create Persona by CI
        if (!$idPersona) {
            $ci = trim($validated['ci']);
            $persona = \Src\Personal\Infrastructure\Persistence\Models\PersonaModel::where('ci', $ci)->first();

            if (!$persona) {
                $persona = \Src\Personal\Infrastructure\Persistence\Models\PersonaModel::create([
                    'nombres'          => mb_strtoupper(trim($validated['nombres'])),
                    'primer_apellido'  => mb_strtoupper(trim($validated['primer_apellido'])),
                    'segundo_apellido' => !empty($validated['segundo_apellido']) ? mb_strtoupper(trim($validated['segundo_apellido'])) : null,
                    'ci'               => $ci,
                    'activo'           => true,
                ]);
            } else {
                $persona->update([
                    'nombres'          => mb_strtoupper(trim($validated['nombres'])),
                    'primer_apellido'  => mb_strtoupper(trim($validated['primer_apellido'])),
                    'segundo_apellido' => !empty($validated['segundo_apellido']) ? mb_strtoupper(trim($validated['segundo_apellido'])) : $persona->segundo_apellido,
                ]);
            }

            $idPersona = $persona->id;
        } else {
            $persona = \Src\Personal\Infrastructure\Persistence\Models\PersonaModel::find($idPersona);
        }

        // Determine username & password (default to CI)
        $defaultCredential = $persona ? $persona->ci : ($validated['ci'] ?? 'user' . rand(1000, 9999));
        $username = !empty($validated['username']) ? trim($validated['username']) : $defaultCredential;
        $password = !empty($validated['password']) ? $validated['password'] : $defaultCredential;

        // Check if username or persona already has a user account
        $existingUser = UserModel::where('username', $username)
            ->orWhere('id_persona', $idPersona)
            ->first();

        if ($existingUser) {
            return ApiResponse::error("Ya existe una cuenta de usuario para este funcionario (Usuario: {$existingUser->username}).", 422);
        }

        $user = UserModel::create([
            'username'              => $username,
            'password'              => \Illuminate\Support\Facades\Hash::make($password),
            'id_persona'            => $idPersona,
            'id_sede_scope'         => $validated['id_sede_scope'] ?? null,
            'activo'                => true,
            'debe_cambiar_password' => false,
        ]);
        
        if (!empty($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }
        
        return ApiResponse::success($user->load(['roles.sistema', 'permissions.sistema', 'persona', 'sede']), 'Usuario creado correctamente');
    }
}
