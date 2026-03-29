<?php

namespace Src\Personal\Application\Create;

final class CreatePersonaCommand
{
    public function __construct(
        public readonly string $primerApellido,
        public readonly string $segundoApellido, // I'll keep it required for now as per Claude, if change, change it.
        public readonly string $nombres,
        public readonly string $ci,
        public readonly string $idCiExpedido,
        public readonly string $idSexo,
        public readonly string $celularPersonal,
        public readonly string $correoPersonal,
        public readonly string $estadoCivil,
        public readonly string $idNacionalidad,
        public readonly string $idCiudad,
        public readonly string $idPais, // Fixed: Added required idPais
        public readonly ?string $complemento = null,
        public readonly ?string $direccionDomicilio = null,
    ) {}
}
