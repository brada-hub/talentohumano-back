<?php

namespace Src\Auth\Infrastructure\Persistence;

use Illuminate\Support\Facades\Hash;
use Src\Auth\Domain\Entities\User;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;
use Src\Auth\Infrastructure\Persistence\Models\UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByUsername(string $username): ?User
    {
        $model = UserModel::with(['roles.permissions', 'roles.sistema'])->where('username', $username)->first();

        if (!$model) {
            return null;
        }

        return new User(
            id: $model->id_user,
            personaId: $model->id_persona,
            username: $model->username,
            password: $model->password,
            activo: (bool) $model->activo,
            roles: $model->roles->toArray()
        );
    }

    public function checkPassword(string $inputPassword, string $userPassword): bool
    {
        return Hash::check($inputPassword, $userPassword);
    }

    public function createToken(User $user): string
    {
        /** @var UserModel $model */
        $model = UserModel::find($user->id());
        return $model->createToken('auth_token')->plainTextToken;
    }

    public function deleteCurrentToken(): void
    {
        auth()->user()?->currentAccessToken()->delete();
    }
}
