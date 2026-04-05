<?php

namespace Src\Beneficios\Application;

use Src\Beneficios\Domain\Repositories\BeneficiarioRepositoryInterface;

final class GetBeneficiosCatalogsHandler
{
    public function __construct(
        private readonly BeneficiarioRepositoryInterface $repository
    ) {}

    public function handle(): array
    {
        return $this->repository->getCatalogs();
    }
}
