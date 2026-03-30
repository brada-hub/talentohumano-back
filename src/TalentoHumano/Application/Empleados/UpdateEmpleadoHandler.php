<?php

namespace Src\TalentoHumano\Application\Empleados;

use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;

final class UpdateEmpleadoHandler
{
    public function __construct(
        private readonly EmpleadoRepositoryInterface $repo
    ) {}

    public function handle(int $id, array $data): array
    {
        return $this->repo->updateEmployee($id, $data);
    }
}
