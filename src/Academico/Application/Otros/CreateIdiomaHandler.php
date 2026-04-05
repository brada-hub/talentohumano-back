<?php

namespace Src\Academico\Application\Otros;

use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;

final class CreateIdiomaHandler
{
    public function __construct(private readonly AcademicoRepositoryInterface $repo) {}
    public function handle(string $personaId, array $data): array
    {
        return $this->repo->createIdioma($personaId, $data);
    }
}
