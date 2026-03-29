<?php

namespace Src\TalentoHumano\Domain\Repositories;

use Src\TalentoHumano\Domain\Entities\Empleado;

interface TalentoHumanoRepositoryInterface
{
    public function save(Empleado $empleado): void;
    public function findByPersonaId(string $personaId): ?Empleado;
}
