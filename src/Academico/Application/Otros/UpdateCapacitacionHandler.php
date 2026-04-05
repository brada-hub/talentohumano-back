<?php

namespace Src\Academico\Application\Otros;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class UpdateCapacitacionHandler
{
    public function __construct(private readonly AcademicoRepositoryInterface $repo) {}
    public function handle(int $id, array $data): ?array
    {
        return $this->repo->updateCapacitacion($id, $data);
    }
}
