<?php

namespace Src\Academico\Domain\Repositories;

interface AcademicoRepositoryInterface
{
    public function findProfileByPersonaId(string $personaId): ?array;

    public function createPregrado(string $personaId, array $data): array;

    public function updatePregrado(int $id, array $data): ?array;

    public function deletePregrado(int $id): bool;

    public function createPostgrado(string $personaId, array $data): array;

    public function updatePostgrado(int $id, array $data): ?array;

    public function deletePostgrado(int $id): bool;

    public function createExperienciaDocente(string $personaId, array $data): array;

    public function updateExperienciaDocente(int $id, array $data): ?array;

    public function deleteExperienciaDocente(int $id): bool;

    public function createExperienciaProfesional(string $personaId, array $data): array;

    public function updateExperienciaProfesional(int $id, array $data): ?array;

    public function deleteExperienciaProfesional(int $id): bool;

    public function createCapacitacion(string $personaId, array $data): array;

    public function updateCapacitacion(int $id, array $data): ?array;

    public function deleteCapacitacion(int $id): bool;

    public function createIdioma(string $personaId, array $data): array;

    public function updateIdioma(int $id, array $data): ?array;

    public function deleteIdioma(int $id): bool;

    public function createProduccionIntelectual(string $personaId, array $data): array;

    public function updateProduccionIntelectual(int $id, array $data): ?array;

    public function deleteProduccionIntelectual(int $id): bool;

    public function createReconocimiento(string $personaId, array $data): array;

    public function updateReconocimiento(int $id, array $data): ?array;

    public function deleteReconocimiento(int $id): bool;
}
