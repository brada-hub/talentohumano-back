<?php

namespace Src\TalentoHumano\Application\Contratos;

use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;

final class UploadContratoFirmadoHandler
{
    public function __construct(
        private readonly EmpleadoRepositoryInterface $repo
    ) {
    }

    public function handle(int $empleadoId, int $contratoId, array $fileData): array
    {
        return $this->repo->uploadContratoFirmado($empleadoId, $contratoId, $fileData);
    }
}
