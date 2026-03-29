<?php

namespace Src\Onboarding\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Onboarding\Domain\Repositories\OnboardingRepositoryInterface;
use Src\Onboarding\Infrastructure\Persistence\EloquentOnboardingRepository;
use Src\Onboarding\Application\Register\CompleteOnboardingHandler;

final class OnboardingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            OnboardingRepositoryInterface::class,
            EloquentOnboardingRepository::class
        );

        $this->app->bind(CompleteOnboardingHandler::class, function ($app) {
            return new CompleteOnboardingHandler(
                $app->make(OnboardingRepositoryInterface::class)
            );
        });
    }
}
