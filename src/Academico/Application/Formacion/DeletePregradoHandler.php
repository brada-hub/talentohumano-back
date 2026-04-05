<?php

namespace Src\Academico\Application\Formacion;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class DeletePregradoHandler
{
    public function __construct(
        private readonly AcademicoRepositoryInterface $repo,
    ) {}

    public function handle(int $id): bool
    {
        return $this->repo->deletePregrado($id);
    }
}
