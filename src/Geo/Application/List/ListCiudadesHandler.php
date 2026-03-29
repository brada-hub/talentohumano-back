<?php

namespace Src\Geo\Application\List;

use Src\Geo\Domain\Repositories\GeoRepositoryInterface;

class ListCiudadesHandler
{
    public function __construct(
        private readonly GeoRepositoryInterface $repository
    ) {}

    public function handle(int $departamentoId): array
    {
        return $this->repository->listCiudades($departamentoId);
    }
}
