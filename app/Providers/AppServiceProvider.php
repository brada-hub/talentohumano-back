<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(\Src\Auth\Infrastructure\Providers\AuthServiceProvider::class);
        $this->app->register(\Src\Personal\Infrastructure\Providers\PersonalServiceProvider::class);
        $this->app->register(\Src\Onboarding\Infrastructure\Providers\OnboardingServiceProvider::class);
        $this->app->register(\Src\TalentoHumano\Infrastructure\Providers\TalentoHumanoServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'user' => \Src\Auth\Infrastructure\Persistence\Models\UserModel::class,
        ]);
    }
}
