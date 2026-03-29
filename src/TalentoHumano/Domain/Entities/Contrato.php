<?php

namespace Src\TalentoHumano\Domain\Entities;

use DateTime;
use Exception;

class Contrato
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $empleadoId,
        private readonly int $tipoContratoId,
        private readonly int $areaId,
        private readonly int $cargoId,
        private readonly float $salario,
        private readonly DateTime $fechaInicio,
        private readonly ?DateTime $fechaFin,
        private readonly int $sedeId,
        private readonly string $estadoContrato = 'Activo'
    ) {
        if ($this->fechaFin && $this->fechaFin < $this->fechaInicio) {
            throw new Exception("La fecha de fin debe ser posterior a la fecha de inicio.");
        }
    }

    public function toArray(): array
    {
        return [
            'id_contrato'      => $this->id,
            'id_empleado'      => $this->empleadoId,
            'id_tipo_contrato' => $this->tipoContratoId,
            'id_area'          => $this->areaId,
            'id_cargo'         => $this->cargoId,
            'salario'          => $this->salario,
            'fecha_inicio'     => $this->fechaInicio->format('Y-m-d'),
            'fecha_fin'        => $this->fechaFin ? $this->fechaFin->format('Y-m-d') : null,
            'id_sede'          => $this->sedeId,
            'estado_contrato'  => $this->estadoContrato,
        ];
    }
}
