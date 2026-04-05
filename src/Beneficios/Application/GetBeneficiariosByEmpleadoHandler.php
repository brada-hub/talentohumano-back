<?php

namespace Src\Beneficios\Application;

use Src\Beneficios\Domain\Repositories\BeneficiarioRepositoryInterface;

final class GetBeneficiariosByEmpleadoHandler
{
    public function __construct(
        private readonly BeneficiarioRepositoryInterface $repository
    ) {}

    public function handle(int $empleadoId): array
    {
        return $this->repository->getByEmpleado($empleadoId);
    }
}
