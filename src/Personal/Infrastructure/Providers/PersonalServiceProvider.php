<?php

namespace Src\Personal\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Personal\Application\Create\CreatePersonaHandler;
use Src\Personal\Domain\Repositories\PersonaRepositoryInterface;
use Src\Personal\Infrastructure\Persistence\EloquentPersonaRepository;

final class PersonalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PersonaRepositoryInterface::class,
            EloquentPersonaRepository::class
        );

        $this->app->bind(CreatePersonaHandler::class, function ($app) {
            return new CreatePersonaHandler(
                $app->make(PersonaRepositoryInterface::class)
            );
        });
    }
}
