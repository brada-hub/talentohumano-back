<?php

namespace Src\Geo\Domain\Entities;

class Pais
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $nombre,
        private readonly string $iso2,
        private readonly string $iso3,
        private readonly string $prefijo,
        private readonly bool $activo = true
    ) {}

    public function id(): ?int { return $this->id; }
    public function nombre(): string { return $this->nombre; }
    public function iso2(): string { return $this->iso2; }
    public function iso3(): string { return $this->iso3; }
    public function prefijo(): string { return $this->prefijo; }
    public function activo(): bool { return $this->activo; }

    public function toArray(): array
    {
        return [
            'id_pais'            => $this->id,
            'nombre'             => $this->nombre,
            'iso2'               => $this->iso2,
            'iso3'               => $this->iso3,
            'prefijo_telefonico' => $this->prefijo,
            'activo'             => $this->activo,
        ];
    }
}
