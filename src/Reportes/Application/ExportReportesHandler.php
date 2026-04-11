<?php

namespace Src\Reportes\Application;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use Src\Reportes\Domain\Repositories\ReporteRepositoryInterface;

final class ExportReportesHandler
{
    public function __construct(
        private readonly ReporteRepositoryInterface $repo
    ) {
    }

    public function handle(array $filters, string $section, string $format): array
    {
        $dashboard = $this->repo->getDashboard($filters);

        $format = strtolower(trim($format));
        if (!in_array($format, ['excel', 'word', 'pdf'], true)) {
            throw new InvalidArgumentException('Formato de exportacion no valido.');
        }

        $section = strtolower(trim($section));
        $report = $this->resolveSection($dashboard, $section);

        $filenameBase = 'reporte_' . $section . '_' . Carbon::now()->format('Ymd_His');

        return match ($format) {
            'excel' => [
                'filename' => $filenameBase . '.xls',
                'content_type' => 'application/vnd.ms-excel; charset=UTF-8',
                'binary' => $this->buildExcelContent($report, $dashboard),
            ],
            'word' => [
                'filename' => $filenameBase . '.doc',
                'content_type' => 'application/msword; charset=UTF-8',
                'binary' => $this->buildWordContent($report, $dashboard),
            ],
            'pdf' => [
                'filename' => $filenameBase . '.pdf',
                'content_type' => 'application/pdf',
                'binary' => $this->buildPdfContent($report, $dashboard),
            ],
        };
    }

    private function resolveSection(array $dashboard, string $section): array
    {
        return match ($section) {
            'sedes' => [
                'title' => 'Personal por sede',
                'headers' => ['Sede', 'Personal activo'],
                'rows' => array_map(fn ($item) => [
                    $item['nombre'] ?? '',
                    $item['total'] ?? 0,
                ], $dashboard['personal_por_sede'] ?? []),
            ],
            'contratos' => [
                'title' => 'Contratos vigentes',
                'headers' => ['Funcionario', 'Tipo', 'Cargo', 'Area', 'Sede', 'Inicio', 'Fin', 'Vigencia', 'Salario', 'Correo institucional'],
                'rows' => array_map(fn ($item) => [
                    $item['nombre_completo'] ?? '',
                    $item['tipo_contrato'] ?? '',
                    $item['cargo'] ?? '',
                    $item['area'] ?? '',
                    $item['sede'] ?? '',
                    $this->formatDate($item['fecha_inicio'] ?? null),
                    $this->formatDate($item['fecha_fin'] ?? null),
                    $this->formatContractValidity($item['dias_restantes'] ?? null),
                    $this->formatCurrency($item['salario'] ?? null),
                    $item['correo_institucional'] ?? 'Sin correo institucional',
                ], $dashboard['contratos_vigentes'] ?? []),
            ],
            'beneficiarios' => [
                'title' => 'Beneficiarios',
                'headers' => ['Funcionario', 'Total', 'Detalle'],
                'rows' => array_map(fn ($item) => [
                    $item['nombre_completo'] ?? '',
                    $item['total_beneficiarios'] ?? 0,
                    collect($item['beneficiarios'] ?? [])->map(
                        fn ($beneficiario) => ($beneficiario['parentesco'] ?? '') . ': ' . ($beneficiario['nombre_completo'] ?? '')
                    )->implode('; '),
                ], $dashboard['beneficiarios'] ?? []),
            ],
            'academico' => [
                'title' => 'Resumen academico',
                'headers' => ['Funcionario', 'Pregrado', 'Postgrado', 'Docencia', 'Exp. profesional', 'Capacitaciones', 'Total'],
                'rows' => array_map(fn ($item) => [
                    $item['nombre_completo'] ?? '',
                    $item['pregrado'] ?? 0,
                    $item['postgrado'] ?? 0,
                    $item['docencia'] ?? 0,
                    $item['experiencia_profesional'] ?? 0,
                    $item['capacitaciones'] ?? 0,
                    $item['total_registros'] ?? 0,
                ], $dashboard['academico'] ?? []),
            ],
            'legajo' => [
                'title' => 'Documentos faltantes del legajo',
                'headers' => ['Funcionario', 'Faltantes', 'Cobertura', 'Severidad', 'Categorias faltantes'],
                'rows' => array_map(fn ($item) => [
                    $item['nombre_completo'] ?? '',
                    ($item['total_faltantes'] ?? 0) . ' de ' . ($item['total_requeridas'] ?? 0),
                    ($item['cobertura_porcentaje'] ?? 0) . '%',
                    $item['severidad'] ?? 'Sin dato',
                    implode(', ', $item['categorias_faltantes'] ?? []),
                ], $dashboard['documentos_faltantes'] ?? []),
            ],
            'recordatorios' => [
                'title' => 'Recordatorios institucionales',
                'headers' => ['Tipo', 'Funcionario', 'Fecha', 'Dias restantes', 'Detalle', 'Correo institucional'],
                'rows' => array_merge(
                    array_map(fn ($item) => [
                        'Cumpleanos',
                        $item['nombre_completo'] ?? '',
                        $this->formatDate($item['proxima_fecha'] ?? null),
                        $item['dias_restantes'] ?? 0,
                        ($item['sede'] ?? 'Sin sede') . ' / cumple ' . ($item['edad_cumplida'] ?? 'N/R') . ' anos',
                        $item['correo_institucional'] ?? 'Sin correo institucional',
                    ], $dashboard['recordatorios']['cumpleanios'] ?? []),
                    array_map(fn ($item) => [
                        'Aniversario',
                        $item['nombre_completo'] ?? '',
                        $this->formatDate($item['proxima_fecha'] ?? null),
                        $item['dias_restantes'] ?? 0,
                        ($item['sede'] ?? 'Sin sede') . ' / ' . ($item['anios_cumplidos'] ?? 0) . ' anos de servicio',
                        $item['correo_institucional'] ?? 'Sin correo institucional',
                    ], $dashboard['recordatorios']['aniversarios'] ?? []),
                ),
            ],
            default => throw new InvalidArgumentException('Seccion de reporte no valida.'),
        };
    }

    private function buildExcelContent(array $report, array $dashboard): string
    {
        return "\xEF\xBB\xBF" . $this->buildHtmlDocument($report, $dashboard, 'excel');
    }

    private function buildWordContent(array $report, array $dashboard): string
    {
        return $this->buildHtmlDocument($report, $dashboard, 'word');
    }

    private function buildPdfContent(array $report, array $dashboard): string
    {
        $html = $this->buildHtmlDocument($report, $dashboard, 'pdf');
        $orientation = count($report['headers'] ?? []) > 5 ? 'landscape' : 'portrait';

        return Pdf::loadHTML($html)
            ->setPaper('letter', $orientation)
            ->output();
    }

    private function buildHtmlDocument(array $report, array $dashboard, string $format): string
    {
        $filtersLabel = $this->buildFiltersLabel(
            $dashboard['filters'] ?? [],
            $dashboard['catalogs']['sedes'] ?? [],
            $dashboard['catalogs']['areas'] ?? [],
        );

        return View::make('reportes.export', [
            'title' => $report['title'] ?? 'Reporte',
            'subtitle' => $this->resolveSubtitle($format),
            'summary' => $this->buildSummary($report),
            'headers' => $report['headers'] ?? [],
            'rows' => $report['rows'] ?? [],
            'generatedAt' => Carbon::now()->format('d/m/Y H:i'),
            'filtersLabel' => $filtersLabel,
            'recordsCount' => count($report['rows'] ?? []),
            'format' => $format,
        ])->render();
    }

    private function resolveSubtitle(string $format): string
    {
        return match ($format) {
            'pdf' => 'Reporte institucional para seguimiento ejecutivo',
            'word' => 'Documento editable para revisión administrativa',
            default => 'Matriz tabular para análisis y consolidación',
        };
    }

    private function buildSummary(array $report): string
    {
        $count = count($report['rows'] ?? []);
        $title = strtolower($report['title'] ?? 'reporte');

        return match ($title) {
            'personal por sede' => "Distribución actual del personal activo por sede institucional. Total de filas: {$count}.",
            'contratos vigentes' => "Relación del personal con contrato vigente según filtros aplicados. Total de contratos: {$count}.",
            'beneficiarios' => "Resumen de derechohabientes registrados por funcionario. Total de filas: {$count}.",
            'resumen academico' => "Consolidado de trayectoria académica y profesional del personal. Total de filas: {$count}.",
            'documentos faltantes del legajo' => "Control de categorías documentales pendientes en el legajo, con severidad y cobertura por funcionario. Total de filas: {$count}.",
            'recordatorios institucionales' => "Próximos eventos institucionales vinculados al personal. Total de filas: {$count}.",
            default => "Resumen institucional del reporte seleccionado. Total de filas: {$count}.",
        };
    }

    private function formatDate(mixed $date): string
    {
        if (empty($date)) {
            return '---';
        }

        return Carbon::parse((string) $date)->format('d/m/Y');
    }

    private function formatCurrency(mixed $amount): string
    {
        if ($amount === null || $amount === '') {
            return '---';
        }

        return 'Bs. ' . number_format((float) $amount, 2, ',', '.');
    }

    private function formatContractValidity(mixed $days): string
    {
        if ($days === null || $days === '') {
            return 'Indefinida o sin fecha fin';
        }

        $days = (int) $days;

        if ($days < 0) {
            return 'Vencido hace ' . abs($days) . ' dias';
        }

        if ($days === 0) {
            return 'Vence hoy';
        }

        if ($days <= 30) {
            return 'Vence en ' . $days . ' dias';
        }

        return 'Vigente por ' . $days . ' dias mas';
    }

    private function buildFiltersLabel(array $filters, array $sedes, array $areas): string
    {
        $monthName = null;
        if (!empty($filters['mes'])) {
            $monthName = Carbon::create()->month((int) $filters['mes'])->locale('es')->monthName;
        }

        $sedeLabel = collect($sedes)->firstWhere('value', $filters['id_sede'] ?? null)['label'] ?? null;
        $areaLabel = collect($areas)->firstWhere('value', $filters['id_area'] ?? null)['label'] ?? null;

        $labels = collect([
            $monthName ? 'Mes: ' . ucfirst($monthName) : null,
            $sedeLabel ? 'Sede: ' . $sedeLabel : 'Sede: Todas',
            $areaLabel ? 'Area: ' . $areaLabel : 'Area: Todas',
        ])->filter();

        return $labels->implode(' | ');
    }
}
