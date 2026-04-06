<?php

namespace Src\Recordatorios\Infrastructure\Persistence;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Src\Recordatorios\Domain\Repositories\RecordatorioRepositoryInterface;
use Src\Recordatorios\Infrastructure\Mail\CumpleaniosInstitucionalMail;
use Src\Recordatorios\Infrastructure\Persistence\Models\RecordatorioEnviadoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\AreaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EmpleadoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\SedeModel;

final class EloquentRecordatorioRepository implements RecordatorioRepositoryInterface
{
    public function getResumen(array $filters = []): array
    {
        $today = Carbon::today();
        $selectedMonth = $this->resolveMonth($filters);
        $empleados = $this->loadEmpleados($filters);

        return [
            'filtros' => [
                'mes' => $selectedMonth,
                'id_sede' => isset($filters['id_sede']) && $filters['id_sede'] !== '' ? (int) $filters['id_sede'] : null,
                'id_area' => isset($filters['id_area']) && $filters['id_area'] !== '' ? (int) $filters['id_area'] : null,
            ],
            'catalogos' => $this->buildCatalogos(),
            'cumpleanios' => $this->buildCumpleanios($empleados, $today, $selectedMonth),
            'aniversarios' => $this->buildAniversarios($empleados, $today, $selectedMonth),
            'contratos_por_vencer' => $this->buildContratosPorVencer($empleados, $today, $selectedMonth),
            'recordatorios_enviados' => $this->buildHistorial($filters),
        ];
    }

    public function sendCumpleanios(int $empleadoId, bool $automatico = false, bool $force = false): array
    {
        $today = Carbon::today();
        $empleado = EmpleadoModel::with([
            'persona',
            'contratoActivo.area',
            'contratoActivo.cargo',
            'contratoActivo.sede',
        ])->find($empleadoId);

        if (!$empleado || !$empleado->persona || !$empleado->persona->fecha_nacimiento) {
            return [
                'success' => false,
                'message' => 'No se encontro informacion suficiente para enviar el recordatorio',
            ];
        }

        $birthDate = Carbon::parse($empleado->persona->fecha_nacimiento);
        $eventDate = $birthDate->copy()->year($today->year);

        $existing = RecordatorioEnviadoModel::query()
            ->where('id_empleado', $empleado->id_empleado)
            ->where('tipo', 'cumpleanios')
            ->whereDate('fecha_evento', $eventDate->toDateString())
            ->where('estado', 'enviado')
            ->latest('id_recordatorio')
            ->first();

        if ($existing && !$force) {
            return [
                'success' => true,
                'already_sent' => true,
                'message' => 'La felicitacion de cumpleanios ya fue enviada para este evento',
                'recordatorio' => $existing->toArray(),
            ];
        }

        $destinatario = $empleado->correo_institucional;

        if (!$destinatario) {
            $record = RecordatorioEnviadoModel::create([
                'id_empleado' => $empleado->id_empleado,
                'id_persona' => $empleado->persona->id,
                'tipo' => 'cumpleanios',
                'canal' => 'correo',
                'fecha_evento' => $eventDate->toDateString(),
                'automatico' => $automatico,
                'estado' => 'fallido',
                'error' => 'El empleado no cuenta con correo institucional registrado',
                'payload' => $this->buildBirthdayPayload($empleado, $eventDate),
            ]);

            return [
                'success' => false,
                'message' => 'No existe un correo institucional disponible para enviar la felicitacion',
                'recordatorio' => $record->toArray(),
            ];
        }

        $payload = $this->buildBirthdayPayload($empleado, $eventDate);
        $asunto = 'UNITEPC - Feliz Cumpleanios';

        try {
            Mail::to($destinatario)->send(new CumpleaniosInstitucionalMail([
                ...$payload,
                'asunto' => $asunto,
            ]));

            $record = RecordatorioEnviadoModel::create([
                'id_empleado' => $empleado->id_empleado,
                'id_persona' => $empleado->persona->id,
                'tipo' => 'cumpleanios',
                'canal' => 'correo',
                'destinatario' => $destinatario,
                'asunto' => $asunto,
                'fecha_evento' => $eventDate->toDateString(),
                'automatico' => $automatico,
                'estado' => 'enviado',
                'enviado_en' => now(),
                'payload' => $payload,
            ]);

            return [
                'success' => true,
                'already_sent' => false,
                'message' => 'Felicitacion enviada correctamente',
                'recordatorio' => $record->toArray(),
            ];
        } catch (\Throwable $exception) {
            $record = RecordatorioEnviadoModel::create([
                'id_empleado' => $empleado->id_empleado,
                'id_persona' => $empleado->persona->id,
                'tipo' => 'cumpleanios',
                'canal' => 'correo',
                'destinatario' => $destinatario,
                'asunto' => $asunto,
                'fecha_evento' => $eventDate->toDateString(),
                'automatico' => $automatico,
                'estado' => 'fallido',
                'error' => $exception->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'message' => 'No se pudo enviar la felicitacion institucional',
                'recordatorio' => $record->toArray(),
            ];
        }
    }

    public function sendCumpleaniosDelDia(): array
    {
        $today = Carbon::today();

        $empleados = EmpleadoModel::with([
            'persona',
            'contratoActivo.area',
            'contratoActivo.cargo',
            'contratoActivo.sede',
        ])
            ->whereHas('persona', function ($query) use ($today) {
                $query->whereMonth('fecha_nacimiento', $today->month)
                    ->whereDay('fecha_nacimiento', $today->day);
            })
            ->get();

        return $empleados
            ->map(fn ($empleado) => $this->sendCumpleanios((int) $empleado->id_empleado, true))
            ->values()
            ->all();
    }

    private function buildCumpleanios(Collection $empleados, Carbon $today, int $selectedMonth): array
    {
        $withBirthdays = $empleados
            ->filter(fn ($empleado) => !empty($empleado->persona?->fecha_nacimiento))
            ->map(function ($empleado) use ($today) {
                $birthDate = Carbon::parse($empleado->persona->fecha_nacimiento);
                $nextBirthday = $birthDate->copy()->year($today->year);

                if ($nextBirthday->lt($today)) {
                    $nextBirthday->addYear();
                }

                return [
                    'id_empleado' => $empleado->id_empleado,
                    'id_persona' => $empleado->persona?->id,
                    'nombre_completo' => trim(collect([
                        $empleado->persona?->primer_apellido,
                        $empleado->persona?->segundo_apellido,
                        $empleado->persona?->nombres,
                    ])->filter()->implode(' ')),
                    'fecha_nacimiento' => $birthDate->format('Y-m-d'),
                    'fecha_evento' => $nextBirthday->format('Y-m-d'),
                    'fecha_evento_legible' => $nextBirthday->format('d/m'),
                    'edad_que_cumple' => $nextBirthday->year - $birthDate->year,
                    'dias_restantes' => $today->diffInDays($nextBirthday, false),
                    'cargo' => $empleado->contratoActivo?->cargo?->nombre_cargo,
                    'area' => $empleado->contratoActivo?->area?->nombre_area,
                    'sede' => $empleado->contratoActivo?->sede?->nombre,
                    'correo_institucional' => $empleado->correo_institucional,
                ];
            })
            ->sortBy([
                ['dias_restantes', 'asc'],
                ['nombre_completo', 'asc'],
            ])
            ->values();

        return [
            'hoy' => $withBirthdays->filter(fn ($item) => $item['dias_restantes'] === 0)->values()->all(),
            'semana' => $withBirthdays->filter(fn ($item) => $item['dias_restantes'] >= 0 && $item['dias_restantes'] <= 7)->values()->all(),
            'mes' => $withBirthdays->filter(fn ($item) => Carbon::parse($item['fecha_evento'])->month === $selectedMonth)->values()->all(),
            'proximos' => $withBirthdays->filter(fn ($item) => $item['dias_restantes'] >= 0)->take(20)->values()->all(),
        ];
    }

    private function buildAniversarios(Collection $empleados, Carbon $today, int $selectedMonth): array
    {
        $withAnniversaries = $empleados
            ->filter(fn ($empleado) => !empty($empleado->contratoActivo?->fecha_inicio))
            ->map(function ($empleado) use ($today) {
                $startDate = Carbon::parse($empleado->contratoActivo->fecha_inicio);
                $nextAnniversary = $startDate->copy()->year($today->year);

                if ($nextAnniversary->lt($today)) {
                    $nextAnniversary->addYear();
                }

                return [
                    'id_empleado' => $empleado->id_empleado,
                    'nombre_completo' => trim(collect([
                        $empleado->persona?->primer_apellido,
                        $empleado->persona?->segundo_apellido,
                        $empleado->persona?->nombres,
                    ])->filter()->implode(' ')),
                    'fecha_inicio' => $startDate->format('Y-m-d'),
                    'fecha_evento' => $nextAnniversary->format('Y-m-d'),
                    'fecha_evento_legible' => $nextAnniversary->format('d/m'),
                    'anios_que_cumple' => $nextAnniversary->year - $startDate->year,
                    'dias_restantes' => $today->diffInDays($nextAnniversary, false),
                    'cargo' => $empleado->contratoActivo?->cargo?->nombre_cargo,
                    'area' => $empleado->contratoActivo?->area?->nombre_area,
                    'sede' => $empleado->contratoActivo?->sede?->nombre,
                ];
            })
            ->filter(fn ($item) => $item['anios_que_cumple'] > 0)
            ->sortBy([
                ['dias_restantes', 'asc'],
                ['nombre_completo', 'asc'],
            ])
            ->values();

        return [
            'hoy' => $withAnniversaries->filter(fn ($item) => $item['dias_restantes'] === 0)->values()->all(),
            'mes' => $withAnniversaries->filter(fn ($item) => Carbon::parse($item['fecha_evento'])->month === $selectedMonth)->values()->all(),
            'proximos' => $withAnniversaries->filter(fn ($item) => $item['dias_restantes'] >= 0)->take(20)->values()->all(),
        ];
    }

    private function buildContratosPorVencer(Collection $empleados, Carbon $today, int $selectedMonth): array
    {
        $contracts = $empleados
            ->filter(fn ($empleado) => !empty($empleado->contratoActivo?->fecha_fin))
            ->map(function ($empleado) use ($today) {
                $fechaFin = Carbon::parse($empleado->contratoActivo->fecha_fin);

                return [
                    'id_empleado' => $empleado->id_empleado,
                    'id_contrato' => $empleado->contratoActivo?->id_contrato,
                    'nombre_completo' => trim(collect([
                        $empleado->persona?->primer_apellido,
                        $empleado->persona?->segundo_apellido,
                        $empleado->persona?->nombres,
                    ])->filter()->implode(' ')),
                    'fecha_fin' => $fechaFin->format('Y-m-d'),
                    'fecha_fin_legible' => $fechaFin->format('d/m/Y'),
                    'dias_restantes' => $today->diffInDays($fechaFin, false),
                    'cargo' => $empleado->contratoActivo?->cargo?->nombre_cargo,
                    'area' => $empleado->contratoActivo?->area?->nombre_area,
                    'sede' => $empleado->contratoActivo?->sede?->nombre,
                    'tipo_contrato' => $empleado->contratoActivo?->tipo?->nombre,
                ];
            })
            ->filter(fn ($item) => $item['dias_restantes'] >= 0)
            ->sortBy([
                ['dias_restantes', 'asc'],
                ['nombre_completo', 'asc'],
            ])
            ->values();

        return [
            '7_dias' => $contracts->filter(fn ($item) => $item['dias_restantes'] <= 7)->values()->all(),
            '30_dias' => $contracts->filter(fn ($item) => $item['dias_restantes'] <= 30)->values()->all(),
            'mes' => $contracts->filter(fn ($item) => Carbon::parse($item['fecha_fin'])->month === $selectedMonth)->values()->all(),
            'proximos' => $contracts->take(20)->values()->all(),
        ];
    }

    private function buildHistorial(array $filters = []): array
    {
        $query = RecordatorioEnviadoModel::query();

        $empleadoIds = $this->resolveEmpleadoIdsForFilters($filters);

        if ($empleadoIds !== null) {
            $query->whereIn('id_empleado', $empleadoIds);
        }

        return $query
            ->latest('id_recordatorio')
            ->limit(50)
            ->get()
            ->map(function (RecordatorioEnviadoModel $record) {
                return [
                    'id_recordatorio' => $record->id_recordatorio,
                    'id_empleado' => $record->id_empleado,
                    'id_persona' => $record->id_persona,
                    'tipo' => $record->tipo,
                    'canal' => $record->canal,
                    'destinatario' => $record->destinatario,
                    'asunto' => $record->asunto,
                    'fecha_evento' => optional($record->fecha_evento)->format('Y-m-d'),
                    'automatico' => $record->automatico,
                    'estado' => $record->estado,
                    'enviado_en' => optional($record->enviado_en)->format('Y-m-d H:i:s'),
                    'error' => $record->error,
                    'payload' => $record->payload,
                ];
            })
            ->all();
    }

    private function buildBirthdayPayload(EmpleadoModel $empleado, Carbon $eventDate): array
    {
        $birthDate = Carbon::parse($empleado->persona->fecha_nacimiento);

        return [
            'nombre_completo' => trim(collect([
                $empleado->persona?->primer_apellido,
                $empleado->persona?->segundo_apellido,
                $empleado->persona?->nombres,
            ])->filter()->implode(' ')),
            'fecha_evento' => $eventDate->toDateString(),
            'fecha_evento_legible' => $eventDate->format('d/m/Y'),
            'edad_que_cumple' => $eventDate->year - $birthDate->year,
            'cargo' => $empleado->contratoActivo?->cargo?->nombre_cargo,
            'area' => $empleado->contratoActivo?->area?->nombre_area,
            'sede' => $empleado->contratoActivo?->sede?->nombre,
            'correo_institucional' => $empleado->correo_institucional,
            'correo_personal' => $empleado->persona->correo_personal,
        ];
    }

    private function loadEmpleados(array $filters = []): Collection
    {
        $query = EmpleadoModel::with([
            'persona',
            'contratoActivo.area',
            'contratoActivo.cargo',
            'contratoActivo.sede',
            'contratoActivo.tipo',
        ]);

        if (!empty($filters['id_sede'])) {
            $sedeId = (int) $filters['id_sede'];
            $query->whereHas('contratoActivo', fn ($q) => $q->where('id_sede', $sedeId));
        }

        if (!empty($filters['id_area'])) {
            $areaId = (int) $filters['id_area'];
            $query->whereHas('contratoActivo', fn ($q) => $q->where('id_area', $areaId));
        }

        return $query->get();
    }

    private function buildCatalogos(): array
    {
        return [
            'meses' => [
                ['value' => 1, 'label' => 'Enero'],
                ['value' => 2, 'label' => 'Febrero'],
                ['value' => 3, 'label' => 'Marzo'],
                ['value' => 4, 'label' => 'Abril'],
                ['value' => 5, 'label' => 'Mayo'],
                ['value' => 6, 'label' => 'Junio'],
                ['value' => 7, 'label' => 'Julio'],
                ['value' => 8, 'label' => 'Agosto'],
                ['value' => 9, 'label' => 'Septiembre'],
                ['value' => 10, 'label' => 'Octubre'],
                ['value' => 11, 'label' => 'Noviembre'],
                ['value' => 12, 'label' => 'Diciembre'],
            ],
            'sedes' => SedeModel::query()
                ->orderBy('nombre')
                ->get(['id_sede', 'nombre'])
                ->map(fn (SedeModel $sede) => [
                    'value' => (int) $sede->id_sede,
                    'label' => $sede->nombre,
                ])
                ->values()
                ->all(),
            'areas' => AreaModel::query()
                ->orderBy('nombre_area')
                ->get(['id_area', 'nombre_area'])
                ->map(fn (AreaModel $area) => [
                    'value' => (int) $area->id_area,
                    'label' => $area->nombre_area,
                ])
                ->values()
                ->all(),
        ];
    }

    private function resolveMonth(array $filters): int
    {
        $month = isset($filters['mes']) && $filters['mes'] !== '' ? (int) $filters['mes'] : (int) Carbon::today()->month;

        return $month >= 1 && $month <= 12 ? $month : (int) Carbon::today()->month;
    }

    private function resolveEmpleadoIdsForFilters(array $filters): ?array
    {
        if (empty($filters['id_sede']) && empty($filters['id_area'])) {
            return null;
        }

        return $this->loadEmpleados($filters)
            ->pluck('id_empleado')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
