<?php

namespace Src\Geo\Domain\Repositories;

interface GeoRepositoryInterface
{
    public function listPaises(): array;
    public function listDepartamentos(int $paisId): array;
    public function listCiudades(int $departamentoId): array;
    public function listNacionalidades(): array;
}
