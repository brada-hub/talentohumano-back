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
            // General error to prevent leaking info
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
        return ApiResponse::success($user, 'Datos del usuario autenticado');
    }
}
