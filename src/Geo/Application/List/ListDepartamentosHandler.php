<?php

namespace Src\Geo\Application\List;

use Src\Geo\Domain\Repositories\GeoRepositoryInterface;

class ListDepartamentosHandler
{
    public function __construct(
        private readonly GeoRepositoryInterface $repository
    ) {}

    public function handle(int $paisId): array
    {
        return $this->repository->listDepartamentos($paisId);
    }
}
