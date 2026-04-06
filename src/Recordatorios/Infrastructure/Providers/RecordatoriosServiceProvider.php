<?php

namespace Src\Recordatorios\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Recordatorios\Domain\Repositories\RecordatorioRepositoryInterface;
use Src\Recordatorios\Infrastructure\Console\Commands\SendBirthdayGreetingsCommand;
use Src\Recordatorios\Infrastructure\Persistence\EloquentRecordatorioRepository;

final class RecordatoriosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RecordatorioRepositoryInterface::class, EloquentRecordatorioRepository::class);
    }

    public function boot(): void
    {
        $this->commands([
            SendBirthdayGreetingsCommand::class,
        ]);
    }
}
