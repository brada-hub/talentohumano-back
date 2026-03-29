<?php

namespace Src\Geo\Domain\Entities;

class Nacionalidad
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $gentilicio,
        private readonly int $paisId
    ) {}

    public function id(): ?int { return $this->id; }
    public function gentilicio(): string { return $this->gentilicio; }
    public function paisId(): int { return $this->paisId; }

    public function toArray(): array
    {
        return [
            'id_nacionalidad' => $this->id,
            'gentilicio'      => $this->gentilicio,
            'id_pais'         => $this->paisId,
        ];
    }
}
