<?php

namespace Src\TalentoHumano\Domain\Entities;

final class Empleado
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $personaId, // UUID from Personas
        private readonly ?string $celularInstitucional,
        private readonly ?string $correoInstitucional,
        private readonly ?int $idCaja,
        private readonly ?string $nroMatriculaSeguro,
        private readonly ?int $idEntidadPensiones,
        private readonly ?string $nroNuaCua,
        private readonly string $estadoLaboral = 'Activo',
        private readonly ?array $personaDatos = [],
        private readonly ?array $contratoActivo = []
    ) {}

    public function id(): ?int { return $this->id; }
    public function personaId(): string { return $this->personaId; }
    public function isActivo(): bool { return $this->estadoLaboral === 'Activo'; }
    public function hasContratoActivo(): bool { return !empty($this->contratoActivo); }

    public function toArray(): array
    {
        return [
            'id_empleado'            => $this->id,
            'id_persona'             => $this->personaId,
            'celular_institucional'  => $this->celularInstitucional,
            'correo_institucional'   => $this->correoInstitucional,
            'id_caja'                => $this->idCaja,
            'nro_matricula_seguro'   => $this->nroMatriculaSeguro,
            'id_entidad_pensiones'   => $this->idEntidadPensiones,
            'nro_nua_cua'            => $this->nroNuaCua,
            'estado_laboral'         => $this->estadoLaboral,
            'persona'                => $this->personaDatos,
            'contratoActivo'         => $this->contratoActivo,
        ];
    }
}
