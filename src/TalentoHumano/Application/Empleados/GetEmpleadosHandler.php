<?php

namespace Src\TalentoHumano\Application\Empleados;

use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;

final class GetEmpleadosHandler
{
    public function __construct(
        private readonly EmpleadoRepositoryInterface $repo
    ) {}

    public function handle(): array
    {
        return $this->repo->findAllActive();
    }
}
