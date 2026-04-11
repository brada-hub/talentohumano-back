<?php

namespace Src\TalentoHumano\Application\Contratos;

use Carbon\Carbon;
use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;

abstract class AbstractBuildContratoTemplateDataHandler
{
    public function __construct(
        protected readonly EmpleadoRepositoryInterface $repo
    ) {
    }

    protected function toCarbon(null|string|Carbon $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }

    protected function formatDateLiteral(?Carbon $date): ?string
    {
        return $date ? $date->locale('es')->translatedFormat('d \\d\\e F \\d\\e Y') : null;
    }

    protected function buildDuracionLiteral(?Carbon $inicio, ?Carbon $fin): ?string
    {
        if (!$inicio || !$fin || $fin->lt($inicio)) {
            return null;
        }

        $inclusiveEnd = $fin->copy()->addDay();
        $diff = $inicio->diff($inclusiveEnd);
        $parts = [];

        if ($diff->m > 0) {
            $parts[] = $this->formatCountLiteral($diff->m, 'mes', 'meses');
        }

        if ($diff->d > 0) {
            $parts[] = $this->formatCountLiteral($diff->d, 'día', 'días');
        }

        return implode(' y ', $parts ?: ['cero (0) días']);
    }

    protected function buildLugarTrabajo(array $contrato): ?string
    {
        $sede = $contrato['sede']['nombre'] ?? null;

        if (!$sede) {
            return null;
        }

        return str_starts_with(mb_strtolower($sede), 'campus')
            ? 'el ' . $sede
            : 'la ciudad de ' . $sede;
    }

    protected function buildFullNameNamesFirst(array $persona): string
    {
        return trim(collect([
            $persona['nombres'] ?? null,
            $persona['primer_apellido'] ?? null,
            $persona['segundo_apellido'] ?? null,
        ])->filter()->implode(' '));
    }

    protected function buildNationalityLabel(array $persona, ?string $sexo): string
    {
        $nacionalidad = trim((string) ($persona['nacionalidad']['nacionalidad'] ?? 'Boliviana'));

        if ($nacionalidad === '') {
            return 'boliviana';
        }

        $normalizedSexo = mb_strtolower(trim((string) $sexo));
        $isFemale = str_contains($normalizedSexo, 'femen');

        if (mb_strtolower($nacionalidad) === 'boliviana' || mb_strtolower($nacionalidad) === 'boliviano') {
            return $isFemale ? 'boliviana' : 'boliviano';
        }

        return mb_strtolower($nacionalidad);
    }

    protected function formatSalaryLiteral(float $value): string
    {
        $integer = (int) floor($value);
        $decimals = (int) round(($value - $integer) * 100);
        $literal = ucfirst($this->numberToWords($integer));

        return "({$literal} " . str_pad((string) $decimals, 2, '0', STR_PAD_LEFT) . '/100 Bolivianos)';
    }

    protected function formatSalaryNumeral(float $value): string
    {
        return 'Bs. ' . number_format($value, 0, '.', '.') . '.-';
    }

    protected function formatHoursLiteral(int $hours): string
    {
        return $this->numberToWords($hours) . "({$hours})";
    }

    protected function formatCountLiteral(int $number, string $singular, string $plural): string
    {
        $unit = $number === 1 ? $singular : $plural;

        return $this->numberToWords($number) . " ({$number}) {$unit}";
    }

    protected function buildHerederos(array $beneficiarios): array
    {
        return collect($beneficiarios)
            ->map(function (array $beneficiario) {
                $fechaNac = $this->toCarbon($beneficiario['fecha_nacimiento'] ?? null);

                return [
                    'parentesco' => $beneficiario['parentesco']['nombre'] ?? $beneficiario['parentesco']['nombre_parentesco'] ?? 'Beneficiario',
                    'nombre' => trim(collect([
                        $beneficiario['nombres'] ?? null,
                        $beneficiario['primer_apellido'] ?? $beneficiario['apellido_paterno'] ?? null,
                        $beneficiario['segundo_apellido'] ?? $beneficiario['apellido_materno'] ?? null,
                    ])->filter()->implode(' ')),
                    'edad' => $fechaNac ? $fechaNac->age : null,
                    'ci' => trim(($beneficiario['ci'] ?? '') . ' ' . ($beneficiario['complemento'] ?? '')),
                    'expedido' => $beneficiario['expedido']['codigo_expedido'] ?? $beneficiario['expedido']['nombre'] ?? null,
                ];
            })
            ->values()
            ->all();
    }

    protected function numberToWords(int $number): string
    {
        $units = [
            0 => 'cero', 1 => 'un', 2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco',
            6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve', 10 => 'diez',
            11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce', 15 => 'quince',
            16 => 'dieciseis', 17 => 'diecisiete', 18 => 'dieciocho', 19 => 'diecinueve',
            20 => 'veinte', 21 => 'veintiun', 22 => 'veintidos', 23 => 'veintitres',
            24 => 'veinticuatro', 25 => 'veinticinco', 26 => 'veintiseis', 27 => 'veintisiete',
            28 => 'veintiocho', 29 => 'veintinueve',
        ];

        $tens = [
            30 => 'treinta', 40 => 'cuarenta', 50 => 'cincuenta', 60 => 'sesenta',
            70 => 'setenta', 80 => 'ochenta', 90 => 'noventa',
        ];

        $hundreds = [
            100 => 'cien', 200 => 'doscientos', 300 => 'trescientos', 400 => 'cuatrocientos',
            500 => 'quinientos', 600 => 'seiscientos', 700 => 'setecientos',
            800 => 'ochocientos', 900 => 'novecientos',
        ];

        if ($number < 30) {
            return $units[$number];
        }

        if ($number < 100) {
            $ten = intdiv($number, 10) * 10;
            $rest = $number % 10;

            return $rest === 0 ? $tens[$ten] : $tens[$ten] . ' y ' . $this->numberToWords($rest);
        }

        if ($number < 1000) {
            if ($number === 100) {
                return 'cien';
            }

            $hundred = intdiv($number, 100) * 100;
            $rest = $number % 100;
            $prefix = $hundred === 100 ? 'ciento' : $hundreds[$hundred];

            return $rest === 0 ? $prefix : $prefix . ' ' . $this->numberToWords($rest);
        }

        if ($number < 1000000) {
            $thousands = intdiv($number, 1000);
            $rest = $number % 1000;
            $prefix = $thousands === 1 ? 'mil' : $this->numberToWords($thousands) . ' mil';

            return $rest === 0 ? $prefix : $prefix . ' ' . $this->numberToWords($rest);
        }

        if ($number < 1000000000) {
            $millions = intdiv($number, 1000000);
            $rest = $number % 1000000;
            $prefix = $millions === 1 ? 'un millon' : $this->numberToWords($millions) . ' millones';

            return $rest === 0 ? $prefix : $prefix . ' ' . $this->numberToWords($rest);
        }

        return (string) $number;
    }

    protected function mergeRecursiveDistinct(array $base, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = $this->mergeRecursiveDistinct($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    protected function buildBasePayload(
        array $persona,
        array $empleado,
        array $contrato,
        array $gramatica,
        ?Carbon $fechaInicio,
        ?Carbon $fechaFin,
        ?Carbon $fechaFirma,
        array $overrides = []
    ): array {
        $sexo = $persona['sexo']['sexo'] ?? null;
        $fechaNacimiento = $this->toCarbon($persona['fecha_nacimiento'] ?? null);
        $edad = $fechaNacimiento ? $fechaNacimiento->age : null;
        $salarioNumeral = (float) (data_get($overrides, 'contrato.salario_numeral') ?? $contrato['salario'] ?? 0);
        $cargaHorariaNumeral = (int) (data_get($overrides, 'contrato.carga_horaria_numeral') ?? 48);

        return [
            'empresa' => [
                'razon_social' => 'ASOCIACIÓN CIVIL UNIVERSIDAD TÉCNICA PRIVADA COSMOS – UNITEPC',
                'domicilio_legal' => 'la Av. Blanco Galindo Km 7 ½, Zona Florida Norte de la ciudad de Cochabamba',
                'representante_legal' => 'Lic. Eduardo E. Mancilla Heredia',
                'apoderado_actual' => 'Lic. Brayan Cabeño Zambrana',
                'apoderado_tratamiento' => 'el',
                'apoderado_ci' => '8004816',
                'apoderado_expedido' => 'Cbba.',
            ],
            'trabajador' => [
                'nombre_completo' => $this->buildFullNameNamesFirst($persona),
                'edad' => $edad,
                'ci' => trim(($persona['ci'] ?? '') . ' ' . ($persona['complemento'] ?? '')),
                'expedido' => $persona['expedido']['nombre'] ?? null,
                'expedido_sigla' => $persona['expedido']['sigla'] ?? null,
                'estado_civil' => mb_strtolower((string) ($persona['estado_civil'] ?? '')),
                'nacionalidad' => $this->buildNationalityLabel($persona, $sexo),
                'domicilio' => $persona['direccion_domicilio'] ?? null,
                'departamento_domicilio' => $persona['departamento_domicilio'] ?? null,
                'sexo' => $sexo,
            ],
            'contrato' => [
                'id_contrato' => $contrato['id_contrato'] ?? null,
                'cargo' => $contrato['cargo']['nombre_cargo'] ?? null,
                'dependencia' => $contrato['area']['nombre_area'] ?? null,
                'gestion_academica' => data_get($overrides, 'contrato.gestion_academica') ?? '2026',
                'duracion_literal' => $this->buildDuracionLiteral($fechaInicio, $fechaFin),
                'fecha_inicio' => $fechaInicio?->format('Y-m-d'),
                'fecha_fin' => $fechaFin?->format('Y-m-d'),
                'fecha_inicio_literal' => $this->formatDateLiteral($fechaInicio),
                'fecha_fin_literal' => $this->formatDateLiteral($fechaFin),
                'lugar_trabajo' => data_get($overrides, 'contrato.lugar_trabajo') ?? $this->buildLugarTrabajo($contrato),
                'carga_horaria_numeral' => $cargaHorariaNumeral,
                'carga_horaria_literal' => $this->formatHoursLiteral($cargaHorariaNumeral),
                'horarios' => data_get($overrides, 'contrato.horarios') ?? [
                    'De lunes a viernes de 07:45 a 12:30 y de 14:30 a 18:30',
                    'Sábados de 08:15 a 12:30.',
                ],
                'nota_horaria' => data_get($overrides, 'contrato.nota_horaria') ?? 'Toda vez que existen cuatro (4) horas adicionales, estas serán utilizadas durante cada semana previo acuerdo de partes y bajo supervisión e instrucción de su inmediato superior.',
                'salario_numeral' => $salarioNumeral,
                'salario_numeral_formateado' => $this->formatSalaryNumeral($salarioNumeral),
                'salario_literal_parentesis' => $this->formatSalaryLiteral($salarioNumeral),
                'remuneracion_detalle' => data_get($overrides, 'contrato.remuneracion_detalle') ?: 'La referida suma de dinero será pagada en la moneda señalada y establecido por ley, mediante depósito bancario, en una cuenta bancaria y cuyo titular es ' . $gramatica['el_trabajador'] . '.',
                'bono_frontera_texto' => data_get($overrides, 'contrato.bono_frontera_texto') ?? null,
                'total_ganado_texto' => data_get($overrides, 'contrato.total_ganado_texto') ?? null,
                'referida_suma_texto' => data_get($overrides, 'contrato.referida_suma_texto') ?: 'El salario establecido reconoce todos y cada uno de los derechos reconocidos por las normas laborales vigentes.',
                'ciudad_firma' => data_get($overrides, 'contrato.ciudad_firma') ?? $contrato['sede']['nombre'] ?? 'Cochabamba',
                'fecha_firma' => $fechaFirma?->format('Y-m-d'),
                'fecha_firma_literal' => $this->formatDateLiteral($fechaFirma),
            ],
            'funciones' => data_get($overrides, 'funciones') ?: [
                'Planificación de la gestión académica.',
                'Seguimiento de la gestión planificada.',
                'Cumplir con las demás funciones que sean asignadas por su inmediato superior.',
            ],
            'herederos' => $this->buildHerederos($empleado['beneficiarios'] ?? []),
            'gramatica' => $gramatica,
        ];
    }
}
