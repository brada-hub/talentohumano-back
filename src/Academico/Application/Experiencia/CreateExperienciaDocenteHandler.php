<?php

namespace Src\Academico\Application\Experiencia;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class CreateExperienciaDocenteHandler
{
    public function __construct(
        private readonly AcademicoRepositoryInterface $repo,
    ) {}

    public function handle(string $personaId, array $data): array
    {
        return $this->repo->createExperienciaDocente($personaId, $data);
    }
}
