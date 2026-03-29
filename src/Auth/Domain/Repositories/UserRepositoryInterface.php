<?php

namespace Src\Auth\Domain\Repositories;

use Src\Auth\Domain\Entities\User;

interface UserRepositoryInterface
{
    public function findByUsername(string $username): ?User;
    public function checkPassword(string $inputPassword, string $userPassword): bool;
    public function createToken(User $user): string;
    public function deleteCurrentToken(): void;
}
