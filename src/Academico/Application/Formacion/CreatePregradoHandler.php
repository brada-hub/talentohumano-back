<?php

namespace Src\Academico\Application\Formacion;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class CreatePregradoHandler
{
    public function __construct(
        private readonly AcademicoRepositoryInterface $repo,
    ) {}

    public function handle(string $personaId, array $data): array
    {
        return $this->repo->createPregrado($personaId, $data);
    }
}
