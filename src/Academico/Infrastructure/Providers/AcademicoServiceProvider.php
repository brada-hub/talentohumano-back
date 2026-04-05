<?php

namespace Src\Academico\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class AcademicoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \Src\Academico\Domain\Repositories\AcademicoRepositoryInterface::class,
            \Src\Academico\Infrastructure\Persistence\EloquentAcademicoRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
