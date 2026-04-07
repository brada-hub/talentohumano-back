<?php

namespace Src\TalentoHumano\Domain\Repositories;

use Src\TalentoHumano\Domain\Entities\Empleado;

interface EmpleadoRepositoryInterface
{
    public function findAllActive(): array;
    public function findByIdWithDetails(int $id): ?array;
    public function isPersonAlreadyEmployee(string $personaId): bool;
    /**
     * @return array The full employee data including relations
     */
    public function createEmployeeWithContract(array $personaData, array $empleadoData, ?array $contratoData): array;
    public function getDashboardStats(): array;
    public function findPersonaCvDetails(string $personaId): ?array;
    public function getAttachments(array $personaDetails): array;
    public function updateEmployee(int $id, array $data): array;
    public function findContratoPreviewData(int $empleadoId, ?int $contratoId = null): ?array;
    public function persistGeneratedContract(int $empleadoId, int $contratoId, array $payload, string $pdfBinary, string $filename): array;
}
