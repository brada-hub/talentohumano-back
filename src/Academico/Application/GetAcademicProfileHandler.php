<?php

namespace Src\Academico\Application;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class GetAcademicProfileHandler
{
    public function __construct(
        private readonly AcademicoRepositoryInterface $repo,
    ) {}

    public function handle(string $personaId): ?array
    {
        return $this->repo->findProfileByPersonaId($personaId);
    }
}
