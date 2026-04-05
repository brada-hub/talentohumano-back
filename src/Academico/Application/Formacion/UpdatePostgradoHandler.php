<?php

namespace Src\Academico\Application\Formacion;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class UpdatePostgradoHandler
{
    public function __construct(
        private readonly AcademicoRepositoryInterface $repo,
    ) {}

    public function handle(int $id, array $data): ?array
    {
        return $this->repo->updatePostgrado($id, $data);
    }
}
