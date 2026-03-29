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
        $this->app->register(\Src\Personal\Infrastructure\Providers\PersonalServiceProvider::class);
        $this->app->register(\Src\Onboarding\Infrastructure\Providers\OnboardingServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
