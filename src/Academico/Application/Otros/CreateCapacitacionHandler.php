<?php

namespace Src\Academico\Application\Otros;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class CreateCapacitacionHandler
{
    public function __construct(private readonly AcademicoRepositoryInterface $repo) {}
    public function handle(string $personaId, array $data): array
    {
        return $this->repo->createCapacitacion($personaId, $data);
    }
}
