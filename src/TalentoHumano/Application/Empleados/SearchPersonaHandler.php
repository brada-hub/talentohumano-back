<?php

namespace Src\TalentoHumano\Application\Empleados;

use Src\Personal\Domain\Repositories\PersonaRepositoryInterface;
use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;
use InvalidArgumentException;

final class SearchPersonaHandler
{
    public function __construct(
        private readonly PersonaRepositoryInterface $personaRepo,
        private readonly EmpleadoRepositoryInterface $empleadoRepo
    ) {}

    public function handle(string $ci): array
    {
        $persona = $this->personaRepo->findByCi($ci);

        if (!$persona) {
            throw new InvalidArgumentException("Persona no encontrada");
        }

        $isEmpleado = $this->empleadoRepo->isPersonAlreadyEmployee($persona->id()->value());
        
        if ($isEmpleado) {
            throw new InvalidArgumentException("Ya es un Empleado");
        }

        return $persona->toArray();
    }
}
