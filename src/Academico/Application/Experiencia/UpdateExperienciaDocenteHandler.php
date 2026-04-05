<?php

namespace Src\Academico\Application\Experiencia;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class UpdateExperienciaDocenteHandler
{
    public function __construct(
        private readonly AcademicoRepositoryInterface $repo,
    ) {}

    public function handle(int $id, array $data): ?array
    {
        return $this->repo->updateExperienciaDocente($id, $data);
    }
}
