<?php

namespace Src\Reportes\Infrastructure\Persistence;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Src\Reportes\Domain\Repositories\ReporteRepositoryInterface;

final class EloquentReporteRepository implements ReporteRepositoryInterface
{
    public function getDashboard(array $filters = []): array
    {
        $month = max(1, min(12, (int) ($filters['mes'] ?? now()->month)));
        $sedeId = !empty($filters['id_sede']) ? (int) $filters['id_sede'] : null;
        $areaId = !empty($filters['id_area']) ? (int) $filters['id_area'] : null;

        $activeEmployeeIds = $this->activeEmployeeIds($sedeId, $areaId);

        return [
            'filters' => [
                'mes' => $month,
                'id_sede' => $sedeId,
                'id_area' => $areaId,
            ],
            'catalogs' => [
                'sedes' => $this->getSedesCatalog(),
                'areas' => $this->getAreasCatalog(),
            ],
            'stats' => $this->buildStats($activeEmployeeIds, $month, $sedeId, $areaId),
            'personal_por_sede' => $this->getPersonalPorSede($areaId),
            'contratos_vigentes' => $this->getContratosVigentes($sedeId, $areaId),
            'beneficiarios' => $this->getBeneficiariosResumen($activeEmployeeIds),
            'academico' => $this->getAcademicoResumen($sedeId, $areaId),
            'documentos_faltantes' => $this->getLegajoEstadoReport($activeEmployeeIds),
            'recordatorios' => [
                'cumpleanios' => $this->getUpcomingBirthdays($activeEmployeeIds),
                'aniversarios' => $this->getUpcomingAnniversaries($sedeId, $areaId),
            ],
        ];
    }

    private function buildStats(array $activeEmployeeIds, int $month, ?int $sedeId, ?int $areaId): array
    {
        return [
            'empleados_activos' => count($activeEmployeeIds),
            'contratos_activos' => $this->baseActiveContractsQuery($sedeId, $areaId)->count(),
            'beneficiarios_registrados' => empty($activeEmployeeIds)
                ? 0
                : DB::table('beneficiarios')->whereIn('id_empleado', $activeEmployeeIds)->count(),
            'empleados_con_legajo' => empty($activeEmployeeIds)
                ? 0
                : DB::table('legajo_documentos')->whereIn('id_empleado', $activeEmployeeIds)->distinct()->count('id_empleado'),
            'cumpleanios_mes' => empty($activeEmployeeIds)
                ? 0
                : DB::table('empleados as e')
                    ->join('personas as p', 'e.id_persona', '=', 'p.id')
                    ->whereIn('e.id_empleado', $activeEmployeeIds)
                    ->whereMonth('p.fecha_nacimiento', $month)
                    ->count(),
            'aniversarios_mes' => $this->baseActiveContractsQuery($sedeId, $areaId)
                ->whereMonth('c.fecha_inicio', $month)
                ->count(),
        ];
    }

    private function getSedesCatalog(): array
    {
        return DB::table('sedes')
            ->select('id_sede as value', 'nombre as label')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function getAreasCatalog(): array
    {
        return DB::table('areas')
            ->select('id_area as value', 'nombre_area as label')
            ->orderBy('nombre_area')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function getPersonalPorSede(?int $areaId): array
    {
        return DB::table('contratos as c')
            ->join('sedes as s', 'c.id_sede', '=', 's.id_sede')
            ->where('c.estado_contrato', 'Activo')
            ->when($areaId, fn (Builder $query) => $query->where('c.id_area', $areaId))
            ->groupBy('s.id_sede', 's.nombre')
            ->orderByDesc(DB::raw('COUNT(DISTINCT c.id_empleado)'))
            ->selectRaw('s.id_sede, s.nombre, COUNT(DISTINCT c.id_empleado) as total')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function getContratosVigentes(?int $sedeId, ?int $areaId): array
    {
        return $this->baseActiveContractsQuery($sedeId, $areaId)
            ->join('empleados as e', 'c.id_empleado', '=', 'e.id_empleado')
            ->join('personas as p', 'e.id_persona', '=', 'p.id')
            ->leftJoin('tipo_contrato as tc', 'c.id_tipo_contrato', '=', 'tc.id_tipo_contrato')
            ->leftJoin('areas as a', 'c.id_area', '=', 'a.id_area')
            ->leftJoin('cargos as cg', 'c.id_cargo', '=', 'cg.id_cargo')
            ->leftJoin('sedes as s', 'c.id_sede', '=', 's.id_sede')
            ->orderBy('c.fecha_fin')
            ->limit(25)
            ->get([
                'c.id_contrato',
                'c.fecha_inicio',
                'c.fecha_fin',
                'c.salario',
                'c.estado_contrato',
                'tc.nombre as tipo_contrato',
                'a.nombre_area as area',
                'cg.nombre_cargo as cargo',
                's.nombre as sede',
                'p.nombres',
                'p.primer_apellido',
                'p.segundo_apellido',
                'e.correo_institucional',
            ])
            ->map(function ($row) {
                $record = (array) $row;
                $record['nombre_completo'] = $this->employeeFullName($record);
                $record['dias_restantes'] = !empty($record['fecha_fin'])
                    ? Carbon::today()->diffInDays(Carbon::parse($record['fecha_fin']), false)
                    : null;

                return $record;
            })
            ->all();
    }

    private function getBeneficiariosResumen(array $activeEmployeeIds): array
    {
        if (empty($activeEmployeeIds)) {
            return [];
        }

        $rows = DB::table('beneficiarios as b')
            ->join('empleados as e', 'b.id_empleado', '=', 'e.id_empleado')
            ->join('personas as p', 'e.id_persona', '=', 'p.id')
            ->leftJoin('parentesco as pa', 'b.id_parentesco', '=', 'pa.id_parentesco')
            ->whereIn('b.id_empleado', $activeEmployeeIds)
            ->orderBy('p.primer_apellido')
            ->get([
                'b.id_empleado',
                'b.nombres as beneficiario_nombres',
                'b.primer_apellido as beneficiario_primer_apellido',
                'b.segundo_apellido as beneficiario_segundo_apellido',
                'pa.nombre as parentesco',
                'p.nombres',
                'p.primer_apellido',
                'p.segundo_apellido',
            ]);

        return collect($rows)
            ->groupBy('id_empleado')
            ->map(function (Collection $group) {
                $first = (array) $group->first();
                return [
                    'id_empleado' => $first['id_empleado'],
                    'nombre_completo' => $this->employeeFullName($first),
                    'total_beneficiarios' => $group->count(),
                    'beneficiarios' => $group->map(function ($item) {
                        $row = (array) $item;
                        return [
                            'nombre_completo' => trim(($row['beneficiario_nombres'] ?? '') . ' ' . ($row['beneficiario_primer_apellido'] ?? '') . ' ' . ($row['beneficiario_segundo_apellido'] ?? '')),
                            'parentesco' => $row['parentesco'] ?: 'Sin parentesco',
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->take(20)
            ->all();
    }

    private function getAcademicoResumen(?int $sedeId, ?int $areaId): array
    {
        $pregradoSub = DB::table('formacion_pregrado')->selectRaw('id_persona, COUNT(*) as total')->groupBy('id_persona');
        $postgradoSub = DB::table('formacion_postgrado')->selectRaw('id_persona, COUNT(*) as total')->groupBy('id_persona');
        $docenciaSub = DB::table('experiencia_docente')->selectRaw('id_persona, COUNT(*) as total')->groupBy('id_persona');
        $profesionalSub = DB::table('experiencia_profesional')->selectRaw('id_persona, COUNT(*) as total')->groupBy('id_persona');
        $capacitacionSub = DB::table('capacitaciones')->selectRaw('id_persona, COUNT(*) as total')->groupBy('id_persona');

        return $this->baseActiveContractsQuery($sedeId, $areaId)
            ->join('empleados as e', 'c.id_empleado', '=', 'e.id_empleado')
            ->join('personas as p', 'e.id_persona', '=', 'p.id')
            ->leftJoinSub($pregradoSub, 'pre', fn ($join) => $join->on('pre.id_persona', '=', 'p.id'))
            ->leftJoinSub($postgradoSub, 'post', fn ($join) => $join->on('post.id_persona', '=', 'p.id'))
            ->leftJoinSub($docenciaSub, 'doc', fn ($join) => $join->on('doc.id_persona', '=', 'p.id'))
            ->leftJoinSub($profesionalSub, 'prof', fn ($join) => $join->on('prof.id_persona', '=', 'p.id'))
            ->leftJoinSub($capacitacionSub, 'cap', fn ($join) => $join->on('cap.id_persona', '=', 'p.id'))
            ->orderByDesc(DB::raw('(COALESCE(pre.total,0)+COALESCE(post.total,0)+COALESCE(doc.total,0)+COALESCE(prof.total,0)+COALESCE(cap.total,0))'))
            ->limit(25)
            ->get([
                'e.id_empleado',
                'p.nombres',
                'p.primer_apellido',
                'p.segundo_apellido',
                DB::raw('COALESCE(pre.total, 0) as pregrado'),
                DB::raw('COALESCE(post.total, 0) as postgrado'),
                DB::raw('COALESCE(doc.total, 0) as docencia'),
                DB::raw('COALESCE(prof.total, 0) as experiencia_profesional'),
                DB::raw('COALESCE(cap.total, 0) as capacitaciones'),
            ])
            ->map(function ($row) {
                $record = (array) $row;
                $record['nombre_completo'] = $this->employeeFullName($record);
                $record['total_registros'] = (int) $record['pregrado']
                    + (int) $record['postgrado']
                    + (int) $record['docencia']
                    + (int) $record['experiencia_profesional']
                    + (int) $record['capacitaciones'];
                return $record;
            })
            ->all();
    }

    private function getDocumentosFaltantes(array $activeEmployeeIds): array
    {
        if (empty($activeEmployeeIds)) {
            return [];
        }

        $requiredCategories = ['Contrato', 'Identidad', 'EducaciÃ³n'];

        $employees = DB::table('empleados as e')
            ->join('personas as p', 'e.id_persona', '=', 'p.id')
            ->whereIn('e.id_empleado', $activeEmployeeIds)
            ->get(['e.id_empleado', 'p.nombres', 'p.primer_apellido', 'p.segundo_apellido']);

        $legajos = DB::table('legajo_documentos')
            ->whereIn('id_empleado', $activeEmployeeIds)
            ->get(['id_empleado', 'categoria'])
            ->groupBy('id_empleado');

        return collect($employees)
            ->map(function ($employee) use ($legajos, $requiredCategories) {
                $row = (array) $employee;
                $present = collect($legajos->get($row['id_empleado'], []))
                    ->pluck('categoria')
                    ->unique()
                    ->values()
                    ->all();
                $missing = array_values(array_diff($requiredCategories, $present));

                return [
                    'id_empleado' => $row['id_empleado'],
                    'nombre_completo' => $this->employeeFullName($row),
                    'categorias_presentes' => $present,
                    'categorias_faltantes' => $missing,
                    'total_requeridas' => count($requiredCategories),
                    'total_presentes' => count(array_intersect($requiredCategories, $present)),
                    'total_faltantes' => count($missing),
                    'cobertura_porcentaje' => count($requiredCategories) > 0
                        ? (int) round((count(array_intersect($requiredCategories, $present)) / count($requiredCategories)) * 100)
                        : 0,
                    'severidad' => match (count($missing)) {
                        0 => 'Completo',
                        1 => 'Bajo',
                        2 => 'Medio',
                        default => 'Alto',
                    },
                ];
            })
            ->filter(fn (array $item) => !empty($item['categorias_faltantes']))
            ->values()
            ->take(25)
            ->all();
    }

    private function getUpcomingBirthdays(array $activeEmployeeIds): array
    {
        if (empty($activeEmployeeIds)) {
            return [];
        }

        $today = Carbon::today();

        return DB::table('empleados as e')
            ->join('personas as p', 'e.id_persona', '=', 'p.id')
            ->leftJoin('contratos as c', function ($join) {
                $join->on('c.id_empleado', '=', 'e.id_empleado')
                    ->where('c.estado_contrato', '=', 'Activo');
            })
            ->leftJoin('sedes as s', 'c.id_sede', '=', 's.id_sede')
            ->whereIn('e.id_empleado', $activeEmployeeIds)
            ->whereNotNull('p.fecha_nacimiento')
            ->get([
                'e.id_empleado',
                'p.nombres',
                'p.primer_apellido',
                'p.segundo_apellido',
                'p.fecha_nacimiento',
                's.nombre as sede',
                'e.correo_institucional',
            ])
            ->map(function ($row) use ($today) {
                $record = (array) $row;
                $birthDate = Carbon::parse($record['fecha_nacimiento']);
                $nextBirthday = $birthDate->copy()->year($today->year);

                if ($nextBirthday->lt($today)) {
                    $nextBirthday->addYear();
                }

                $record['nombre_completo'] = $this->employeeFullName($record);
                $record['proxima_fecha'] = $nextBirthday->format('Y-m-d');
                $record['dias_restantes'] = $today->diffInDays($nextBirthday, false);
                $record['edad_cumplida'] = $birthDate->diffInYears($nextBirthday);
                return $record;
            })
            ->filter(fn (array $item) => $item['dias_restantes'] >= 0 && $item['dias_restantes'] <= 30)
            ->sortBy('dias_restantes')
            ->values()
            ->take(15)
            ->all();
    }

    private function getUpcomingAnniversaries(?int $sedeId, ?int $areaId): array
    {
        $today = Carbon::today();

        return $this->baseActiveContractsQuery($sedeId, $areaId)
            ->join('empleados as e', 'c.id_empleado', '=', 'e.id_empleado')
            ->join('personas as p', 'e.id_persona', '=', 'p.id')
            ->leftJoin('sedes as s', 'c.id_sede', '=', 's.id_sede')
            ->get([
                'e.id_empleado',
                'c.fecha_inicio',
                'p.nombres',
                'p.primer_apellido',
                'p.segundo_apellido',
                's.nombre as sede',
                'e.correo_institucional',
            ])
            ->map(function ($row) use ($today) {
                $record = (array) $row;
                $startDate = Carbon::parse($record['fecha_inicio']);
                $nextAnniversary = $startDate->copy()->year($today->year);

                if ($nextAnniversary->lt($today)) {
                    $nextAnniversary->addYear();
                }

                $record['nombre_completo'] = $this->employeeFullName($record);
                $record['proxima_fecha'] = $nextAnniversary->format('Y-m-d');
                $record['anios_cumplidos'] = $startDate->diffInYears($nextAnniversary);
                $record['dias_restantes'] = $today->diffInDays($nextAnniversary, false);
                return $record;
            })
            ->filter(fn (array $item) => $item['dias_restantes'] >= 0 && $item['dias_restantes'] <= 30)
            ->sortBy('dias_restantes')
            ->values()
            ->take(15)
            ->all();
    }

    private function getLegajoEstadoReport(array $activeEmployeeIds): array
    {
        if (empty($activeEmployeeIds)) {
            return [];
        }

        $requiredCategories = ['Identidad', 'Contrato firmado'];

        $employees = DB::table('empleados as e')
            ->join('personas as p', 'e.id_persona', '=', 'p.id')
            ->whereIn('e.id_empleado', $activeEmployeeIds)
            ->get(['e.id_empleado', 'p.nombres', 'p.primer_apellido', 'p.segundo_apellido']);

        $legajos = DB::table('legajo_documentos')
            ->whereIn('id_empleado', $activeEmployeeIds)
            ->get(['id_empleado', 'categoria'])
            ->groupBy('id_empleado');

        $personaDocs = DB::table('empleados as e')
            ->join('documentos_persona as dp', 'e.id_persona', '=', 'dp.id_persona')
            ->whereIn('e.id_empleado', $activeEmployeeIds)
            ->get(['e.id_empleado'])
            ->groupBy('id_empleado');

        return collect($employees)
            ->map(function ($employee) use ($legajos, $personaDocs, $requiredCategories) {
                $row = (array) $employee;
                $present = collect($legajos->get($row['id_empleado'], []))
                    ->pluck('categoria')
                    ->unique()
                    ->values();

                if ($personaDocs->has($row['id_empleado'])) {
                    $present->push('Identidad');
                }

                $present = $present->unique()->values()->all();
                $missing = array_values(array_diff($requiredCategories, $present));
                $presentCount = count(array_intersect($requiredCategories, $present));
                $coverage = count($requiredCategories) > 0
                    ? (int) round(($presentCount / count($requiredCategories)) * 100)
                    : 100;
                $status = $this->resolveLegajoDocumentStatus($coverage);

                return [
                    'id_empleado' => $row['id_empleado'],
                    'nombre_completo' => $this->employeeFullName($row),
                    'categorias_presentes' => $present,
                    'categorias_faltantes' => $missing,
                    'total_requeridas' => count($requiredCategories),
                    'total_presentes' => $presentCount,
                    'total_faltantes' => count($missing),
                    'cobertura_porcentaje' => $coverage,
                    'severidad' => $status,
                    'estado_documental' => $status,
                ];
            })
            ->sortBy([
                ['cobertura_porcentaje', 'asc'],
                ['nombre_completo', 'asc'],
            ])
            ->values()
            ->take(25)
            ->all();
    }

    private function activeEmployeeIds(?int $sedeId, ?int $areaId): array
    {
        return $this->baseActiveContractsQuery($sedeId, $areaId)
            ->distinct()
            ->pluck('c.id_empleado')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function baseActiveContractsQuery(?int $sedeId, ?int $areaId): Builder
    {
        return DB::table('contratos as c')
            ->where('c.estado_contrato', 'Activo')
            ->when($sedeId, fn (Builder $query) => $query->where('c.id_sede', $sedeId))
            ->when($areaId, fn (Builder $query) => $query->where('c.id_area', $areaId));
    }

    private function resolveLegajoDocumentStatus(int $coverage): string
    {
        if ($coverage >= 100) {
            return 'Completo';
        }

        if ($coverage >= 50) {
            return 'En proceso';
        }

        return 'Crítico';
    }

    private function employeeFullName(array $row): string
    {
        return trim(
            collect([
                $row['nombres'] ?? null,
                $row['primer_apellido'] ?? null,
                $row['segundo_apellido'] ?? null,
            ])->filter()->implode(' ')
        );
    }
}
