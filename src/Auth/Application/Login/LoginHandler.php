<?php

namespace Src\Auth\Application\Login;

use Src\Auth\Domain\Repositories\UserRepositoryInterface;
use Src\Auth\Domain\Exceptions\InvalidCredentialsException;
use Illuminate\Support\Facades\Log;

final class LoginHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function handle(LoginCommand $command): array
    {
        $user = $this->userRepository->findByUsername($command->username);

        // Security: Generic error for both "user not found" and "wrong password"
        if (!$user || !$this->userRepository->checkPassword($command->password, $user->password())) {
            Log::warning("Intento de login fallido para el usuario: {$command->username}");
            throw new InvalidCredentialsException();
        }

        if (!$user->isActivo()) {
            Log::alert("Intento de acceso de usuario INACTIVO registrado: {$command->username}");
            throw new InvalidCredentialsException(); 
        }

        $token = $this->userRepository->createToken($user);

        return [
            'token' => $token,
            'user'  => $user->toArray(),
        ];
    }
}
