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

    public function permissions(): array
    {
        $permissions = [];
        foreach ($this->roles as $role) {
            $perms = is_array($role) ? ($role['permissions'] ?? []) : ($role->permissions ?? []);
            foreach ($perms as $p) {
                if (is_array($p)) {
                    $permissions[] = $p['nombres'] ?? $p['name'] ?? 'error';
                } else {
                    $permissions[] = $p->nombres ?? $p->name ?? 'error';
                }
            }
        }
        return array_unique($permissions);
    }

    public function toArray(): array
    {
        $roleNames = [];
        foreach ($this->roles as $r) {
            if (is_array($r)) {
                $roleNames[] = $r['nombres'] ?? $r['name'] ?? 'error';
            } else {
                $roleNames[] = $r->nombres ?? $r->name ?? 'error';
            }
        }

        return [
            'id_user'     => $this->id,
            'id_persona'  => $this->personaId,
            'username'    => $this->username,
            'activo'      => $this->activo,
            'roles'       => $roleNames,
            'permissions' => $this->permissions(),
        ];
    }
}
