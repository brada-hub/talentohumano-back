<?php

namespace Src\Auth\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Src\Auth\Application\Login\LoginCommand;
use Src\Auth\Application\Login\LoginHandler;
use Src\Auth\Application\Logout\LogoutHandler;
use Src\Auth\Infrastructure\Http\Requests\LoginRequest;
use Src\Shared\Infrastructure\Http\ApiResponse;

class AuthController extends Controller
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

            return ApiResponse::success($result, 'Login successful');
        } catch (Exception $e) {
            return ApiResponse::unauthorized($e->getMessage());
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $this->logoutHandler->handle();
            return ApiResponse::success([], 'Logged out successfully');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    /**
     * Get the authenticated user
     */
    public function me(): JsonResponse
    {
        $user = auth()->user();
        return ApiResponse::success($user, 'Authenticated user data');
    }
}
