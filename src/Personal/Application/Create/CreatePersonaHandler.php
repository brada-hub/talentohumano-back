<?php

namespace Src\Personal\Application\Create;

use Src\Personal\Domain\Entities\Persona;
use Src\Personal\Domain\Repositories\PersonaRepositoryInterface;
use Src\Shared\Domain\ValueObjects\UuidVO;
use Src\Shared\Domain\ValueObjects\CI;
use Src\Shared\Domain\ValueObjects\Email;
use Src\Shared\Domain\ValueObjects\Telefono;
use InvalidArgumentException;

final class CreatePersonaHandler
{
    public function __construct(
        private readonly PersonaRepositoryInterface $repo
    ) {}

    public function handle(CreatePersonaCommand $cmd): string
    {
        if ($this->repo->existsByCi($cmd->ci)) {
            throw new InvalidArgumentException("Ya existe una persona con CI: {$cmd->ci}");
        }

        $id = UuidVO::generate();

        $persona = new Persona(
            id:               $id,
            primerApellido:   $cmd->primerApellido,
            segundoApellido:  $cmd->segundoApellido,
            nombres:          $cmd->nombres,
            ci:               new CI($cmd->ci),
            complemento:      $cmd->complemento,
            idCiExpedido:     $cmd->idCiExpedido,
            idSexo:           $cmd->idSexo,
            celularPersonal:  new Telefono($cmd->celularPersonal),
            correoPersonal:   new Email($cmd->correoPersonal),
            estadoCivil:      $cmd->estadoCivil,
            idNacionalidad:   $cmd->idNacionalidad,
            direccionDomicilio: $cmd->direccionDomicilio,
            idCiudad:         $cmd->idCiudad,
            idPais:           $cmd->idPais, // Fixed: AddedidP pais
        );

        $this->repo->save($persona);

        return $id->value();
    }
}
