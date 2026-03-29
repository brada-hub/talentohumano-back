<?php

namespace Src\TalentoHumano\Application\Empleados;

use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;
use InvalidArgumentException;
use Src\Personal\Domain\Repositories\PersonaRepositoryInterface;

final class CreateEmpleadoHandler
{
    public function __construct(
        private readonly EmpleadoRepositoryInterface $repo,
        private readonly PersonaRepositoryInterface $personaRepo
    ) {}

    public function handle(array $personaData, array $empleadoData, ?array $contratoData): array
    {
        $existingPersona = $this->personaRepo->findByCi($personaData['ci']);

        if ($existingPersona) {
            $isEmpleado = $this->repo->isPersonAlreadyEmployee($existingPersona->id()->value());
            if ($isEmpleado) {
                throw new InvalidArgumentException("Ya existe un empleado con esta CI.");
            }
        }

        return $this->repo->createEmployeeWithContract($personaData, $empleadoData, $contratoData);
    }
}
