<?php

namespace Src\Reportes\Domain\Repositories;

interface ReporteRepositoryInterface
{
    public function getDashboard(array $filters = []): array;
}
