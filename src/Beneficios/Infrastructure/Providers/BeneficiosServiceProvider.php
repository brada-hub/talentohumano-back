<?php

namespace Src\Beneficios\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Beneficios\Application\CreateBeneficiarioHandler;
use Src\Beneficios\Application\DeleteBeneficiarioHandler;
use Src\Beneficios\Application\GetBeneficiariosByEmpleadoHandler;
use Src\Beneficios\Application\GetBeneficiosCatalogsHandler;
use Src\Beneficios\Application\UpdateBeneficiarioHandler;
use Src\Beneficios\Domain\Repositories\BeneficiarioRepositoryInterface;
use Src\Beneficios\Infrastructure\Persistence\EloquentBeneficiarioRepository;

class BeneficiosServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BeneficiarioRepositoryInterface::class, EloquentBeneficiarioRepository::class);

        $this->app->bind(GetBeneficiosCatalogsHandler::class, fn ($app) => new GetBeneficiosCatalogsHandler(
            $app->make(BeneficiarioRepositoryInterface::class)
        ));
        $this->app->bind(GetBeneficiariosByEmpleadoHandler::class, fn ($app) => new GetBeneficiariosByEmpleadoHandler(
            $app->make(BeneficiarioRepositoryInterface::class)
        ));
        $this->app->bind(CreateBeneficiarioHandler::class, fn ($app) => new CreateBeneficiarioHandler(
            $app->make(BeneficiarioRepositoryInterface::class)
        ));
        $this->app->bind(UpdateBeneficiarioHandler::class, fn ($app) => new UpdateBeneficiarioHandler(
            $app->make(BeneficiarioRepositoryInterface::class)
        ));
        $this->app->bind(DeleteBeneficiarioHandler::class, fn ($app) => new DeleteBeneficiarioHandler(
            $app->make(BeneficiarioRepositoryInterface::class)
        ));
    }
}
