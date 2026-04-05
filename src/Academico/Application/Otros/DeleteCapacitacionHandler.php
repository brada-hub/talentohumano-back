<?php

namespace Src\Academico\Application\Otros;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class DeleteCapacitacionHandler
{
    public function __construct(private readonly AcademicoRepositoryInterface $repo) {}
    public function handle(int $id): bool
    {
        return $this->repo->deleteCapacitacion($id);
    }
}
