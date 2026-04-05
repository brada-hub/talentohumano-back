<?php

namespace Src\Beneficios\Application;

use Src\Beneficios\Domain\Repositories\BeneficiarioRepositoryInterface;

final class CreateBeneficiarioHandler
{
    public function __construct(
        private readonly BeneficiarioRepositoryInterface $repository
    ) {}

    public function handle(int $empleadoId, array $data): array
    {
        return $this->repository->create($empleadoId, $data);
    }
}
