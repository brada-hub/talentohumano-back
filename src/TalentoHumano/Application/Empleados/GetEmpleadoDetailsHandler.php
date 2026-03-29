<?php

namespace Src\TalentoHumano\Application\Empleados;

use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;
use InvalidArgumentException;

final class GetEmpleadoDetailsHandler
{
    public function __construct(
        private readonly EmpleadoRepositoryInterface $repo
    ) {}

    public function handle(int $id): array
    {
        $employee = $this->repo->findByIdWithDetails($id);

        if (!$employee) {
            throw new InvalidArgumentException("Empleado no encontrado.");
        }

        return $employee;
    }
}
