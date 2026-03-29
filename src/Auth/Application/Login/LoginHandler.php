<?php

namespace Src\Auth\Application\Login;

use Exception;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;

class LoginHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function handle(LoginCommand $command): array
    {
        $user = $this->userRepository->findByUsername($command->username);

        if (!$user) {
            throw new Exception("Invalid credentials");
        }

        if (!$this->userRepository->checkPassword($command->password, $user->password())) {
            throw new Exception("Invalid credentials");
        }

        if (!$user->activo()) {
            throw new Exception("This user is inactive.");
        }

        $token = $this->userRepository->createToken($user);

        return [
            'token' => $token,
            'user'  => $user->toArray(),
        ];
    }
}
