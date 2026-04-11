<?php

namespace Src\Reportes\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Reportes\Application\ExportReportesHandler;
use Src\Reportes\Application\GetDashboardReportesHandler;
use Src\Reportes\Domain\Repositories\ReporteRepositoryInterface;
use Src\Reportes\Infrastructure\Persistence\EloquentReporteRepository;

final class ReportesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ReporteRepositoryInterface::class, EloquentReporteRepository::class);

        $this->app->bind(GetDashboardReportesHandler::class, fn ($app) => new GetDashboardReportesHandler(
            $app->make(ReporteRepositoryInterface::class)
        ));

        $this->app->bind(ExportReportesHandler::class, fn ($app) => new ExportReportesHandler(
            $app->make(ReporteRepositoryInterface::class)
        ));
    }
}
