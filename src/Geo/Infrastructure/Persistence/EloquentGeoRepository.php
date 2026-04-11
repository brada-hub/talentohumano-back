<?php

namespace Src\Geo\Infrastructure\Persistence;

use Illuminate\Support\Facades\Schema;
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
        $query = PaisModel::query()
            ->orderBy('nombre', 'asc');

        if ($this->hasColumn('paises', 'activo')) {
            $query->where('activo', true);
        }

        $prefijoColumn = $this->firstExistingColumn('paises', ['prefijo_telefonico', 'prefijo']) ?? 'prefijo_telefonico';

        return $query->get()
            ->map(fn($item) => new Pais(
                id: (int) ($item->id_pais ?? $item->id ?? 0),
                nombre: (string) ($item->nombre ?? ''),
                iso2: (string) ($item->iso2 ?? ''),
                iso3: (string) ($item->iso3 ?? ''),
                prefijo: (string) ($item->{$prefijoColumn} ?? ''),
                activo: $this->hasColumn('paises', 'activo') ? (bool) $item->activo : true
            )->toArray())
            ->toArray();
    }

    public function listDepartamentos(int $paisId): array
    {
        $paisForeignKey = $this->firstExistingColumn('departamentos', ['pais_id', 'id_pais']);
        $codigoColumn = $this->firstExistingColumn('departamentos', ['codigo_expedido', 'abreviatura', 'codigo']) ?? 'codigo_expedido';

        if (!$paisForeignKey) {
            return [];
        }

        return DepartamentoModel::where($paisForeignKey, $paisId)
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(fn($item) => new Departamento(
                id: (int) ($item->id_departamento ?? 0),
                nombre: (string) ($item->nombre ?? ''),
                codigoExpedido: (string) ($item->{$codigoColumn} ?? ''),
                paisId: (int) ($item->{$paisForeignKey} ?? 0)
            )->toArray())
            ->toArray();
    }

    public function listCiudades(int $departamentoId): array
    {
        $departamentoForeignKey = $this->firstExistingColumn('ciudades', ['departamento_id', 'id_departamento']);

        if (!$departamentoForeignKey) {
            return [];
        }

        return CiudadModel::where($departamentoForeignKey, $departamentoId)
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(fn($item) => new Ciudad(
                id: (int) ($item->id_ciudad ?? 0),
                nombre: (string) ($item->nombre ?? ''),
                departamentoId: (int) ($item->{$departamentoForeignKey} ?? 0)
            )->toArray())
            ->toArray();
    }

    public function listNacionalidades(): array
    {
        $paisForeignKey = $this->firstExistingColumn('nacionalidades', ['id_pais', 'pais_id']);

        return NacionalidadModel::all()
            ->map(fn($item) => new Nacionalidad(
                id: (int) ($item->id_nacionalidad ?? 0),
                gentilicio: (string) ($item->gentilicio ?? ''),
                paisId: (int) ($paisForeignKey ? ($item->{$paisForeignKey} ?? 0) : 0)
            )->toArray())
            ->toArray();
    }

    private function hasColumn(string $table, string $column): bool
    {
        $columns = $this->getColumns($table);

        return isset($columns[$column]);
    }

    private function firstExistingColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if ($this->hasColumn($table, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function getColumns(string $table): array
    {
        static $cache = [];

        if (array_key_exists($table, $cache)) {
            return $cache[$table];
        }

        try {
            $cache[$table] = array_fill_keys(Schema::getColumnListing($table), true);
        } catch (\Throwable) {
            $cache[$table] = [];
        }

        return $cache[$table];
    }
}
