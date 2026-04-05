<?php

namespace Src\Beneficios\Application;

use Src\Beneficios\Domain\Repositories\BeneficiarioRepositoryInterface;

final class DeleteBeneficiarioHandler
{
    public function __construct(
        private readonly BeneficiarioRepositoryInterface $repository
    ) {}

    public function handle(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
