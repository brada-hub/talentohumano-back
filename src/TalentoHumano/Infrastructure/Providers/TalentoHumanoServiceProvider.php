<?php

namespace Src\TalentoHumano\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class TalentoHumanoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface::class,
            \Src\TalentoHumano\Infrastructure\Persistence\EloquentEmpleadoRepository::class
        );
    }
}
