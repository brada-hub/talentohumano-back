<?php

namespace Src\TalentoHumano\Application\Stats;

use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;

final class GetEmpleadoStatsHandler
{
    public function __construct(
        private readonly EmpleadoRepositoryInterface $repo
    ) {}

    public function handle(): array
    {
        return $this->repo->getDashboardStats();
    }
}
