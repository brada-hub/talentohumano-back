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
        
        // Load roles with permissions
        $user->load(['roles.permissions']);
        
        // Build permissions array from all roles
        $permissions = [];
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[] = $permission->nombres;
            }
        }
        $permissions = array_unique($permissions);
        
        // Build response
        $data = [
            'id_user' => $user->id_user,
            'id_persona' => $user->id_persona,
            'username' => $user->username,
            'activo' => $user->activo,
            'roles' => $user->roles->map(fn($r) => [
                'id_rol' => $r->id_rol,
                'nombres' => $r->nombres,
            ]),
            'permissions' => array_values($permissions),
        ];
        
        return ApiResponse::success($data, 'Datos del usuario autenticado');
    }
}
