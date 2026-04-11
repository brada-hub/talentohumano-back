<?php

namespace Src\Reportes\Application;

use Src\Reportes\Domain\Repositories\ReporteRepositoryInterface;

final class GetDashboardReportesHandler
{
    public function __construct(
        private readonly ReporteRepositoryInterface $repo
    ) {
    }

    public function handle(array $filters = []): array
    {
        return $this->repo->getDashboard($filters);
    }
}
