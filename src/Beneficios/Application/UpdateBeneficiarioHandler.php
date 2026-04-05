<?php

namespace Src\Beneficios\Application;

use Src\Beneficios\Domain\Repositories\BeneficiarioRepositoryInterface;

final class UpdateBeneficiarioHandler
{
    public function __construct(
        private readonly BeneficiarioRepositoryInterface $repository
    ) {}

    public function handle(int $id, array $data): ?array
    {
        return $this->repository->update($id, $data);
    }
}
