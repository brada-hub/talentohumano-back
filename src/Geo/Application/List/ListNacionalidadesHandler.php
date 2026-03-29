<?php

namespace Src\Geo\Application\List;

use Src\Geo\Domain\Repositories\GeoRepositoryInterface;

class ListNacionalidadesHandler
{
    public function __construct(
        private readonly GeoRepositoryInterface $repository
    ) {}

    public function handle(): array
    {
        return $this->repository->listNacionalidades();
    }
}
