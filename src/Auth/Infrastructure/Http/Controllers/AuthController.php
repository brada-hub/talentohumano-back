<?php

namespace Src\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Auth\Application\Login\LoginCommand;
use Src\Auth\Application\Login\LoginHandler;
use Src\Auth\Application\Logout\LogoutHandler;
use Src\Auth\Domain\Exceptions\InvalidCredentialsException;
use Src\Auth\Infrastructure\Http\Requests\LoginRequest;
use Src\Shared\Infrastructure\Http\ApiResponse;

final class AuthController extends Controller
{
    public function __construct(
        private readonly LoginHandler $loginHandler,
        private readonly LogoutHandler $logoutHandler
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        \Illuminate\Support\Facades\Log::info('Intento de login recibido:', ['username' => $request->input('username')]);
        try {
            $command = new LoginCommand(
                $request->input('username'),
                $request->input('password')
            );

            $result = $this->loginHandler->handle($command);

            $userModel = \Src\Auth\Infrastructure\Persistence\Models\UserModel::with([
                'sede', 'persona.sexo', 'roles.permissions', 'roles.sistema', 'permissions.sistema'
            ])->where('username', $request->input('username'))->first();

            $userData = $userModel ? $this->buildUserPayload($userModel) : $result['user'];

            return ApiResponse::success([
                'token' => $result['token'],
                'user'  => $userData,
            ], 'Sesión iniciada correctamente');
        } catch (InvalidCredentialsException $e) {
            return ApiResponse::unauthorized($e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e->getMessage() . "\n" . $e->getTraceAsString());
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $this->logoutHandler->handle();
            return ApiResponse::success([], 'Cierre de sesión exitoso');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    public function me(): JsonResponse
    {
        $user = auth()->user();
        $user->load(['sede', 'persona.sexo', 'roles.permissions', 'roles.sistema', 'permissions.sistema']);
        
        $userData = $this->buildUserPayload($user);
        
        return ApiResponse::success($userData, 'Datos del usuario autenticado');
    }

    private function buildUserPayload(\Src\Auth\Infrastructure\Persistence\Models\UserModel $user): array
    {
        $permissionsBySystem = [];
        $isGlobalAdmin = false;

        // Map known systems by ID in case relationship is not preloaded
        $systemSlugMap = [
            1 => 'sigeth',
            2 => 'sispo',
            3 => 'sigva',
        ];

        foreach ($user->roles as $role) {
            $roleNameUpper = strtoupper(trim($role->nombres ?? ''));
            $sysId = (int)($role->sistema_id ?? 0);

            // ONLY a user with role Administrador / Director in SIGETH (sistema_id: 1) is a true Global Admin
            if ($sysId === 1 && in_array($roleNameUpper, ['ADMINISTRADOR', 'ADMIN', 'SUPER ADMIN', 'SUPERADMIN', 'DIRECTOR (ENCARGADO)'])) {
                $isGlobalAdmin = true;
            }

            $sistema = $role->sistema;
            $sistemaName = $sistema ? $sistema->sistema : ($sysId === 1 ? 'SIGETH' : ($sysId === 2 ? 'SISPO' : ($sysId === 3 ? 'SIGVA' : 'Global')));
            $sistemaSlug = $sistema ? strtolower(str_replace(' ', '_', $sistema->sistema)) : ($systemSlugMap[$sysId] ?? 'global');
            
            if (!isset($permissionsBySystem[$sistemaSlug])) {
                $permissionsBySystem[$sistemaSlug] = [
                    'sistema_id' => $sysId,
                    'sistema' => $sistemaName,
                    'url' => $sistema ? $sistema->url_sistema : null,
                    'roles' => [],
                    'permissions' => []
                ];
            }
            
            $permissionsBySystem[$sistemaSlug]['roles'][] = $role->nombres;
            
            foreach ($role->permissions as $permission) {
                $permissionsBySystem[$sistemaSlug]['permissions'][] = $permission->nombres;
            }
        }

        foreach ($user->permissions as $permission) {
            $sistema = $permission->sistema;
            $sysId = (int)($permission->sistema_id ?? 0);
            $sistemaSlug = $sistema ? strtolower(str_replace(' ', '_', $sistema->sistema)) : ($systemSlugMap[$sysId] ?? 'global');

            if (!isset($permissionsBySystem[$sistemaSlug])) {
                $permissionsBySystem[$sistemaSlug] = [
                    'sistema_id' => $sysId,
                    'sistema' => $sistema ? $sistema->sistema : ($sysId === 1 ? 'SIGETH' : ($sysId === 2 ? 'SISPO' : ($sysId === 3 ? 'SIGVA' : 'Global'))),
                    'url' => $sistema ? $sistema->url_sistema : null,
                    'roles' => [],
                    'permissions' => []
                ];
            }
            $permissionsBySystem[$sistemaSlug]['permissions'][] = $permission->nombres;
        }

        // If user is a verified Global Super Admin, grant full access across all systems
        if ($isGlobalAdmin) {
            $allSystems = \Src\Auth\Infrastructure\Persistence\Models\SistemaModel::with(['permissions', 'roles'])->get();
            foreach ($allSystems as $sys) {
                $sSlug = strtolower(str_replace(' ', '_', $sys->sistema));
                if (!isset($permissionsBySystem[$sSlug])) {
                    $permissionsBySystem[$sSlug] = [
                        'sistema_id' => $sys->id_sistema,
                        'sistema' => $sys->sistema,
                        'url' => $sys->url_sistema,
                        'roles' => ['Administrador'],
                        'permissions' => $sys->permissions->pluck('nombres')->toArray()
                    ];
                } else {
                    if (!in_array('Administrador', $permissionsBySystem[$sSlug]['roles'])) {
                        $permissionsBySystem[$sSlug]['roles'][] = 'Administrador';
                    }
                    $permissionsBySystem[$sSlug]['permissions'] = array_values(array_unique(array_merge(
                        $permissionsBySystem[$sSlug]['permissions'],
                        $sys->permissions->pluck('nombres')->toArray()
                    )));
                }
            }
        }

        // Clean & de-duplicate arrays
        $flatPermissions = [];
        foreach ($permissionsBySystem as $slug => $data) {
            $permissionsBySystem[$slug]['roles'] = array_values(array_unique($data['roles']));
            $permissionsBySystem[$slug]['permissions'] = array_values(array_unique($data['permissions']));
            $flatPermissions = array_merge($flatPermissions, $permissionsBySystem[$slug]['permissions']);
        }

        $flatPermissions = array_values(array_unique($flatPermissions));
        
        // Build basic user response mapping roles specifically for frontend compatibility
        $persona = $user->persona;
        if ($persona && $persona->foto) {
            $persona->foto_url = asset($persona->foto);
        }

        return [
            'id_user' => $user->id_user,
            'id' => $user->id_user,
            'id_persona' => $user->id_persona,
            'username' => $user->username,
            'activo' => (bool)$user->activo,
            'debe_cambiar_password' => (bool)$user->debe_cambiar_password,
            'id_sede_scope' => $user->id_sede_scope,
            'sede' => $user->sede,
            'persona' => $persona,
            'is_global_admin' => $isGlobalAdmin,
            'allowed_systems' => array_values(array_keys($permissionsBySystem)),
            'roles' => $user->roles->map(fn($r) => [
                'id_rol' => $r->id_rol,
                'id' => $r->id_rol,
                'nombres' => $r->nombres,
                'nombre' => $r->nombres,
                'name' => $r->nombres,
                'sistema_id' => $r->sistema_id,
                'sistema' => $r->sistema ? $r->sistema->sistema : null,
            ]),
            'permissions' => $flatPermissions,
            'permisos' => $flatPermissions,
            'access_metadata' => $permissionsBySystem
        ];
    }

    public function changePassword(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:4|confirmed',
        ]);

        $user = auth()->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return ApiResponse::error('La contraseña actual es incorrecta', 422);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password),
            'debe_cambiar_password' => false,
        ]);

        return ApiResponse::success([], 'Contraseña actualizada correctamente');
    }

    public function updateProfile(\Illuminate\Http\Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user->id_persona) {
            return ApiResponse::error('No tienes una persona asociada', 422);
        }

        $validated = $request->validate([
            'celular_personal'    => 'nullable|string',
            'correo_personal'     => 'nullable|email',
            'direccion_domicilio' => 'nullable|string',
        ]);

        $persona = \Src\Personal\Infrastructure\Persistence\Models\PersonaModel::findOrFail($user->id_persona);
        $persona->update($validated);

        return ApiResponse::success($persona, 'Información actualizada correctamente');
    }
}
