<?php

namespace Src\Auth\Domain\Entities;

class User
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?int $personaId,
        private readonly string $username,
        private readonly string $password,
        private readonly bool $activo = true,
        private array $roles = []
    ) {}

    public function id(): ?int { return $this->id; }
    public function personaId(): ?int { return $this->personaId; }
    public function username(): string { return $this->username; }
    public function password(): string { return $this->password; }
    public function activo(): bool { return $this->activo; }
    public function roles(): array { return $this->roles; }

    public function toArray(): array
    {
        return [
            'id_user'    => $this->id,
            'id_persona' => $this->personaId,
            'username'   => $this->username,
            'activo'     => $this->activo,
            'roles'      => $this->roles,
        ];
    }
}
