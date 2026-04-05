<?php

namespace Src\Academico\Application\Experiencia;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class DeleteExperienciaDocenteHandler
{
    public function __construct(
        private readonly AcademicoRepositoryInterface $repo,
    ) {}

    public function handle(int $id): bool
    {
        return $this->repo->deleteExperienciaDocente($id);
    }
}
