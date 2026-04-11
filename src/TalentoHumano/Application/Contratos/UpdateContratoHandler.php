<?php

namespace Src\TalentoHumano\Application\Contratos;

use InvalidArgumentException;
use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;

final class UpdateContratoHandler
{
    public function __construct(
        private readonly EmpleadoRepositoryInterface $repo
    ) {
    }

    public function handle(int $empleadoId, int $contratoId, array $data): array
    {
        if ($empleadoId <= 0 || $contratoId <= 0) {
            throw new InvalidArgumentException('Contrato invalido.');
        }

        return $this->repo->updateContrato($empleadoId, $contratoId, $data);
    }
}
