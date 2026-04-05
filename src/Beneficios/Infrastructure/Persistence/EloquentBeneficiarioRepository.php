<?php

namespace Src\Beneficios\Infrastructure\Persistence;

use Src\Beneficios\Domain\Repositories\BeneficiarioRepositoryInterface;
use Src\Beneficios\Infrastructure\Persistence\Models\BeneficiarioModel;
use Src\Beneficios\Infrastructure\Persistence\Models\ParentescoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EmpleadoModel;
use Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel;
use Src\Geo\Infrastructure\Persistence\Models\PaisModel;
use Illuminate\Support\Collection;
use InvalidArgumentException;

final class EloquentBeneficiarioRepository implements BeneficiarioRepositoryInterface
{
    public function getCatalogs(): array
    {
        $boliviaId = PaisModel::query()->where('iso2', 'BO')->value('id_pais');

        $orderedParentescos = collect(['Esposo(a)', 'Cónyuge', 'Hijo(a)', 'Padre', 'Madre']);
        $parentescos = ParentescoModel::query()
            ->whereIn('nombre', $orderedParentescos->all())
            ->get()
            ->sortBy(function (ParentescoModel $item) use ($orderedParentescos) {
                $position = $orderedParentescos->search($item->nombre);
                return $position === false ? 999 : $position;
            })
            ->values();

        $expedidos = DepartamentoModel::query()
            ->when($boliviaId, fn ($query) => $query->where('pais_id', $boliviaId))
            ->orderByRaw("
                CASE codigo_expedido
                    WHEN 'LP' THEN 1
                    WHEN 'CB' THEN 2
                    WHEN 'SC' THEN 3
                    WHEN 'OR' THEN 4
                    WHEN 'PT' THEN 5
                    WHEN 'TJ' THEN 6
                    WHEN 'CH' THEN 7
                    WHEN 'BE' THEN 8
                    WHEN 'PD' THEN 9
                    WHEN 'EXT' THEN 10
                    ELSE 99
                END
            ")
            ->get();

        return [
            'parentescos' => $parentescos->map(function (ParentescoModel $item) {
                return [
                    'id_parentesco' => $item->id_parentesco,
                    'nombre' => $item->nombre === 'Cónyuge' ? 'Esposo(a)' : $item->nombre,
                ];
            })->toArray(),
            'expedidos' => $expedidos->toArray(),
        ];
    }

    public function getByEmpleado(int $empleadoId): array
    {
        return BeneficiarioModel::query()
            ->with(['parentesco', 'expedido'])
            ->where('id_empleado', $empleadoId)
            ->orderBy('primer_apellido')
            ->orderBy('segundo_apellido')
            ->orderBy('nombres')
            ->get()
            ->toArray();
    }

    public function create(int $empleadoId, array $data): array
    {
        EmpleadoModel::query()->findOrFail($empleadoId);

        $existingCount = BeneficiarioModel::query()
            ->where('id_empleado', $empleadoId)
            ->count();

        if ($existingCount >= 2) {
            throw new InvalidArgumentException('El empleado solo puede registrar hasta 2 beneficiarios.');
        }

        $beneficiario = BeneficiarioModel::query()->create([
            'id_empleado' => $empleadoId,
            'primer_apellido' => $data['primer_apellido'],
            'segundo_apellido' => $data['segundo_apellido'] ?? null,
            'nombres' => $data['nombres'],
            'ci' => $data['ci'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'id_ci_expedido' => $data['id_ci_expedido'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'id_parentesco' => $data['id_parentesco'],
        ]);

        return $beneficiario->load(['parentesco', 'expedido'])->toArray();
    }

    public function update(int $id, array $data): ?array
    {
        $beneficiario = BeneficiarioModel::query()->find($id);
        if (!$beneficiario) {
            return null;
        }

        $beneficiario->fill([
            'primer_apellido' => $data['primer_apellido'],
            'segundo_apellido' => $data['segundo_apellido'] ?? null,
            'nombres' => $data['nombres'],
            'ci' => $data['ci'] ?? null,
            'complemento' => $data['complemento'] ?? null,
            'id_ci_expedido' => $data['id_ci_expedido'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'id_parentesco' => $data['id_parentesco'],
        ]);
        $beneficiario->save();

        return $beneficiario->load(['parentesco', 'expedido'])->toArray();
    }

    public function delete(int $id): bool
    {
        $beneficiario = BeneficiarioModel::query()->find($id);
        if (!$beneficiario) {
            return false;
        }

        $beneficiario->delete();
        return true;
    }
}
