<?php

namespace Src\Auth\Application\Logout;

use Src\Auth\Domain\Repositories\UserRepositoryInterface;

class LogoutHandler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function handle(): void
    {
        $this->userRepository->deleteCurrentToken();
    }
}
