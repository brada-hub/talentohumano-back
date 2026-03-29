<?php

namespace Src\Geo\Infrastructure\Persistence;

use Src\Geo\Domain\Entities\Ciudad;
use Src\Geo\Domain\Entities\Departamento;
use Src\Geo\Domain\Entities\Nacionalidad;
use Src\Geo\Domain\Entities\Pais;
use Src\Geo\Domain\Repositories\GeoRepositoryInterface;
use Src\Geo\Infrastructure\Persistence\Models\CiudadModel;
use Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel;
use Src\Geo\Infrastructure\Persistence\Models\NacionalidadModel;
use Src\Geo\Infrastructure\Persistence\Models\PaisModel;

class EloquentGeoRepository implements GeoRepositoryInterface
{
    public function listPaises(): array
    {
        return PaisModel::where('activo', true)
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(fn($item) => new Pais(
                id: $item->id_pais,
                nombre: $item->nombre,
                iso2: $item->iso2,
                iso3: $item->iso3,
                prefijo: $item->prefijo_telefonico,
                activo: $item->activo
            )->toArray())
            ->toArray();
    }

    public function listDepartamentos(int $paisId): array
    {
        return DepartamentoModel::where('pais_id', $paisId)
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(fn($item) => new Departamento(
                id: $item->id_departamento,
                nombre: $item->nombre,
                codigoExpedido: $item->codigo_expedido,
                paisId: $item->pais_id
            )->toArray())
            ->toArray();
    }

    public function listCiudades(int $departamentoId): array
    {
        return CiudadModel::where('departamento_id', $departamentoId)
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(fn($item) => new Ciudad(
                id: $item->id_ciudad,
                nombre: $item->nombre,
                departamentoId: $item->departamento_id
            )->toArray())
            ->toArray();
    }

    public function listNacionalidades(): array
    {
        return NacionalidadModel::all()
            ->map(fn($item) => new Nacionalidad(
                id: $item->id_nacionalidad,
                gentilicio: $item->gentilicio,
                paisId: $item->id_pais
            )->toArray())
            ->toArray();
    }
}
