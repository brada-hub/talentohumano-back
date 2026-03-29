<?php

namespace Src\Geo\Domain\Entities;

class Departamento
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $nombre,
        private readonly string $codigoExpedido,
        private readonly int $paisId
    ) {}

    public function id(): ?int { return $this->id; }
    public function nombre(): string { return $this->nombre; }
    public function codigoExpedido(): string { return $this->codigoExpedido; }
    public function paisId(): int { return $this->paisId; }

    public function toArray(): array
    {
        return [
            'id_departamento' => $this->id,
            'nombre'          => $this->nombre,
            'codigo_expedido' => $this->codigoExpedido,
            'pais_id'         => $this->paisId,
        ];
    }
}
