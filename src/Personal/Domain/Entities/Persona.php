<?php

namespace Src\Personal\Domain\Entities;

use Src\Shared\Domain\ValueObjects\CI;
use Src\Shared\Domain\ValueObjects\Email;
use Src\Shared\Domain\ValueObjects\Telefono;
use Src\Shared\Domain\ValueObjects\UuidVO;

final class Persona
{
    public function __construct(
        private readonly UuidVO $id,
        private readonly string $primerApellido,
        private readonly ?string $segundoApellido,
        private readonly string $nombres,
        private readonly CI $ci,
        private readonly ?string $complemento,
        private readonly string $idCiExpedido,
        private readonly string $idSexo,
        private readonly Telefono $celularPersonal,
        private readonly Email $correoPersonal,
        private readonly string $estadoCivil,
        private readonly string $idNacionalidad,
        private readonly ?string $direccionDomicilio,
        private readonly string $idCiudad,
        private readonly string $idPais,
        private readonly bool $activo = true
    ) {}

    public function id(): UuidVO { return $this->id; }
    public function fullName(): string { return "{$this->primerApellido} {$this->segundoApellido} {$this->nombres}"; }
    public function ci(): CI { return $this->ci; }
    public function celular(): Telefono { return $this->celularPersonal; }
    public function correo(): Email { return $this->correoPersonal; }

    public function toArray(): array
    {
        return [
            'id'                  => $this->id->value(),
            'primer_apellido'     => $this->primerApellido,
            'segundo_apellido'    => $this->segundoApellido,
            'nombres'             => $this->nombres,
            'ci'                  => $this->ci->value(),
            'complemento'         => $this->complemento,
            'id_ci_expedido'      => $this->idCiExpedido,
            'id_sexo'             => $this->idSexo,
            'celular_personal'    => $this->celularPersonal->value(),
            'correo_personal'     => $this->correoPersonal->value(),
            'estado_civil'        => $this->estadoCivil,
            'id_nacionalidad'     => $this->idNacionalidad,
            'direccion_domicilio' => $this->direccionDomicilio,
            'id_ciudad'           => $this->idCiudad,
            'id_pais'             => $this->idPais,
            'activo'              => $this->activo,
        ];
    }
}
