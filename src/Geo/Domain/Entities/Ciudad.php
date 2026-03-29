<?php

namespace Src\Geo\Domain\Entities;

class Ciudad
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $nombre,
        private readonly int $departamentoId
    ) {}

    public function id(): ?int { return $this->id; }
    public function nombre(): string { return $this->nombre; }
    public function departamentoId(): int { return $this->departamentoId; }

    public function toArray(): array
    {
        return [
            'id_ciudad'       => $this->id,
            'nombre'          => $this->nombre,
            'departamento_id' => $this->departamentoId,
        ];
    }
}
