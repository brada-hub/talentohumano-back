<?php

namespace Src\Academico\Application\Formacion;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class CreatePostgradoHandler
{
    public function __construct(
        private readonly AcademicoRepositoryInterface $repo,
    ) {}

    public function handle(string $personaId, array $data): array
    {
        return $this->repo->createPostgrado($personaId, $data);
    }
}
