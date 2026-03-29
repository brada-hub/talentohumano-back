<?php

namespace Src\Personal\Infrastructure\Persistence;

use Src\Personal\Domain\Entities\Persona;
use Src\Personal\Domain\Repositories\PersonaRepositoryInterface;
use Src\Personal\Infrastructure\Persistence\Models\PersonaModel;
use Src\Shared\Domain\ValueObjects\UuidVO;
use Src\Shared\Domain\ValueObjects\CI;
use Src\Shared\Domain\ValueObjects\Email;
use Src\Shared\Domain\ValueObjects\Telefono;

final class EloquentPersonaRepository implements PersonaRepositoryInterface
{
    public function save(Persona $persona): void
    {
        PersonaModel::create($persona->toArray());
    }

    public function findById(UuidVO $id): ?Persona
    {
        $model = PersonaModel::find($id->value());
        return $model ? $this->toDomain($model) : null;
    }

    public function findByCi(string $ci): ?Persona
    {
        $model = PersonaModel::where('ci', $ci)->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findAll(int $page, int $perPage): array
    {
        $paginator = PersonaModel::where('activo', true)
            ->orderBy('primer_apellido')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'data'  => collect($paginator->items())->map(fn($m) => $this->toDomain($m)->toArray())->toArray(),
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
        ];
    }

    public function existsByCi(string $ci): bool
    {
        return PersonaModel::where('ci', $ci)->exists();
    }

    public function update(Persona $persona): void
    {
        PersonaModel::where('id', $persona->id()->value())
            ->update($persona->toArray());
    }

    public function delete(UuidVO $id): void
    {
        PersonaModel::where('id', $id->value())
            ->update(['activo' => false]);
    }

    private function toDomain(PersonaModel $model): Persona
    {
        return new Persona(
            id:                new UuidVO($model->id),
            primerApellido:    $model->primer_apellido,
            segundoApellido:   $model->segundo_apellido,
            nombres:           $model->nombres,
            ci:                new CI($model->ci),
            complemento:       $model->complemento,
            idCiExpedido:     (string)$model->id_ci_expedido,
            idSexo:           (string)$model->id_sexo,
            celularPersonal:   new Telefono($model->celular_personal),
            correoPersonal:    new Email($model->correo_personal),
            estadoCivil:       $model->estado_civil,
            idNacionalidad:   (string)$model->id_nacionalidad,
            direccionDomicilio: $model->direccion_domicilio,
            idCiudad:         (string)$model->id_ciudad,
            idPais:           (string)$model->id_pais, // Mapping id_pais
        );
    }
}
