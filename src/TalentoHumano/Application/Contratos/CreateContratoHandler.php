<?php

namespace Src\TalentoHumano\Application\Contratos;

use InvalidArgumentException;
use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;

final class CreateContratoHandler
{
    public function __construct(
        private readonly EmpleadoRepositoryInterface $repo
    ) {
    }

    public function handle(int $empleadoId, array $data): array
    {
        if ($empleadoId <= 0) {
            throw new InvalidArgumentException('Empleado invalido.');
        }

        return $this->repo->createContrato($empleadoId, $data);
    }
}
