<?php

namespace Src\Geo\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Geo\Domain\Repositories\GeoRepositoryInterface;
use Src\Geo\Infrastructure\Persistence\EloquentGeoRepository;

class GeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            GeoRepositoryInterface::class,
            EloquentGeoRepository::class
        );
    }
}
