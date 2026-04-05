<?php

namespace Src\Beneficios\Domain\Repositories;

interface BeneficiarioRepositoryInterface
{
    public function getCatalogs(): array;

    public function getByEmpleado(int $empleadoId): array;

    public function create(int $empleadoId, array $data): array;

    public function update(int $id, array $data): ?array;

    public function delete(int $id): bool;
}
