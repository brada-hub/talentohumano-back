<?php

namespace Src\Recordatorios\Application;

use Src\Recordatorios\Domain\Repositories\RecordatorioRepositoryInterface;

final class GetResumenRecordatoriosHandler
{
    public function __construct(
        private readonly RecordatorioRepositoryInterface $repository
    ) {
    }

    public function handle(array $filters = []): array
    {
        return $this->repository->getResumen($filters);
    }
}
