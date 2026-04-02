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

            return ApiResponse::success($result, 'Sesión iniciada correctamente');
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
        
        // Load roles with permissions AND sistema
        $user->load(['roles.permissions', 'roles.sistema']);
        
        $permissionsBySystem = [];
        foreach ($user->roles as $role) {
            $sistema = $role->sistema;
            $sistemaName = $sistema ? $sistema->sistema : 'Global';
            $sistemaSlug = $sistema ? strtolower(str_replace(' ', '_', $sistema->sistema)) : 'global';
            
            if (!isset($permissionsBySystem[$sistemaSlug])) {
                $permissionsBySystem[$sistemaSlug] = [
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
        
        // Final sanitization of arrays
        foreach ($permissionsBySystem as $slug => $data) {
            $permissionsBySystem[$slug]['roles'] = array_values(array_unique($data['roles']));
            $permissionsBySystem[$slug]['permissions'] = array_values(array_unique($data['permissions']));
        }
        
        // Build basic user response mapping roles specifically for frontend compatibility
        $userData = [
            'id_user' => $user->id_user,
            'id_persona' => $user->id_persona,
            'username' => $user->username,
            'activo' => $user->activo,
            'roles' => $user->roles->map(fn($r) => [
                'id_rol' => $r->id_rol,
                'nombres' => $r->nombres,
            ]),
            'access_metadata' => $permissionsBySystem
        ];
        
        return ApiResponse::success($userData, 'Datos del usuario autenticado');
    }
}
