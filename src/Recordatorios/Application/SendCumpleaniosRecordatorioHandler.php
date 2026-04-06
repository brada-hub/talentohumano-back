<?php

namespace Src\Recordatorios\Application;

use Src\Recordatorios\Domain\Repositories\RecordatorioRepositoryInterface;

final class SendCumpleaniosRecordatorioHandler
{
    public function __construct(
        private readonly RecordatorioRepositoryInterface $repository
    ) {
    }

    public function handle(int $empleadoId, bool $automatico = false, bool $force = false): array
    {
        return $this->repository->sendCumpleanios($empleadoId, $automatico, $force);
    }
}
