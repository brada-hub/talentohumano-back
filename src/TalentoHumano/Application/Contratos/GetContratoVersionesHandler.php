<?php

namespace Src\TalentoHumano\Application\Contratos;

use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;

final class GetContratoVersionesHandler
{
    public function __construct(
        private readonly EmpleadoRepositoryInterface $repo
    ) {
    }

    public function handle(int $empleadoId, int $contratoId): array
    {
        return $this->repo->getContratoVersiones($empleadoId, $contratoId);
    }
}
