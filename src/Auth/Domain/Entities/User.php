<?php

namespace Src\Auth\Domain\Entities;

final class User
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $personaId,
        private readonly string $username,
        private readonly string $password,
        private readonly bool $activo = true,
        private readonly array $roles = []
    ) {}

    public function id(): ?int { return $this->id; }
    public function username(): string { return $this->username; }
    public function password(): string { return $this->password; }
    public function isActivo(): bool { return $this->activo; }

    /**
     * Flatten all permissions from roles for easy frontend use.
     */
    public function permissions(): array
    {
        $permissions = [];
        foreach ($this->roles as $role) {
            if (isset($role['permissions'])) {
                foreach ($role['permissions'] as $permission) {
                    $permissions[] = $permission['name'];
                }
            }
        }
        return array_unique($permissions);
    }

    public function toArray(): array
    {
        return [
            'id_user'     => $this->id,
            'id_persona'  => $this->personaId,
            'username'    => $this->username,
            'activo'      => $this->activo,
            'roles'       => array_column($this->roles, 'name'),
            'permissions' => $this->permissions(),
        ];
    }
}
