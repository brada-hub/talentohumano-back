<?php

namespace Src\Auth\Domain\Entities;

class Role
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $nombre,
        private readonly ?int $sistemaId = null
    ) {}

    public function id(): ?int { return $this->id; }
    public function nombre(): string { return $this->nombre; }
    public function sistemaId(): ?int { return $this->sistemaId; }
}
