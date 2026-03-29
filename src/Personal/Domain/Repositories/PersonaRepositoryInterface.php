<?php

namespace Src\Personal\Domain\Repositories;

use Src\Personal\Domain\Entities\Persona;
use Src\Shared\Domain\ValueObjects\UuidVO;

interface PersonaRepositoryInterface
{
    public function save(Persona $persona): void;
    public function findById(UuidVO $id): ?Persona;
    public function findByCi(string $ci): ?Persona;
    public function findAll(int $page, int $perPage): array;
    public function existsByCi(string $ci): bool;
    public function update(Persona $persona): void;
    public function delete(UuidVO $id): void;
}
