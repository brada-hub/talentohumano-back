<?php

namespace Src\TalentoHumano\Application\Contratos;

use InvalidArgumentException;
use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;

final class FinalizeContratoHandler
{
    public function __construct(
        private readonly EmpleadoRepositoryInterface $repo
    ) {
    }

    public function handle(int $empleadoId, int $contratoId, ?string $fechaFin = null): array
    {
        if ($empleadoId <= 0 || $contratoId <= 0) {
            throw new InvalidArgumentException('Contrato invalido.');
        }

        return $this->repo->finalizeContrato($empleadoId, $contratoId, $fechaFin);
    }
}
