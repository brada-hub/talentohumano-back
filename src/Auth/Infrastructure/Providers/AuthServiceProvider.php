<?php

namespace Src\Auth\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Auth\Domain\Repositories\UserRepositoryInterface;
use Src\Auth\Infrastructure\Persistence\EloquentUserRepository;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // No specific boot operations needed for now.
    }
}
