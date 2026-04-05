<?php

namespace Src\Academico\Application\Experiencia;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class UpdateExperienciaProfesionalHandler
{
    public function __construct(
        private readonly AcademicoRepositoryInterface $repo,
    ) {}

    public function handle(int $id, array $data): ?array
    {
        return $this->repo->updateExperienciaProfesional($id, $data);
    }
}
