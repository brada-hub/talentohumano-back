<?php

namespace Src\TalentoHumano\Application\Empleados;

use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;
use InvalidArgumentException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Log;

final class GenerateCvPdfHandler
{
    public function __construct(
        private readonly EmpleadoRepositoryInterface $repo
    ) {}

    /**
     * @return array [ 'pdf_binary' => string, 'filename' => string ]
     */
    public function handle(string $personaId): array
    {
        $details = $this->repo->findPersonaCvDetails($personaId);

        if (!$details) {
            throw new InvalidArgumentException("Persona no encontrada");
        }

        $persona = $details['persona'];
        $empleado = $details['empleado'];

        $cargo = null;
        if ($empleado && $empleado->contratoActivo && $empleado->contratoActivo->cargo) {
            $cargo = $empleado->contratoActivo->cargo->nombre_cargo;
        }

        // Generar QR de verificación
        try {
            $qrCode = QrCode::format('svg')
                ->size(120)
                ->margin(1)
                ->errorCorrection('H')
                ->generate(config('app.url') . '/api/v1/cv/verificar/' . $persona->id);
        } catch (\Throwable $e) {
            Log::warning("No se pudo generar QR con SVG: " . $e->getMessage());
            $qrCode = '';
        }

        // ═══════════════════════════════════════════════════════
        //  RECOPILAR ADJUNTOS
        // ═══════════════════════════════════════════════════════
        $adjuntosRaw = $this->repo->getAttachments($details);
        $adjuntosParaBlade = []; // Imágenes directamente pasadas a blade
        $pdfsParaFusion = [];   // PDFs se fusionarán después

        foreach ($adjuntosRaw as $adj) {
            $path = $adj['path'] ?? null;
            if (!$path || !file_exists($path)) {
                Log::info("Adjunto no encontrado en disco: " . ($path ?? 'null') . " - " . $adj['label']);
                continue;
            }

            if ($adj['type'] === 'image') {
                $adjuntosParaBlade[] = $adj; // Se enviará con su 'path' absoluto original
            } else {
                $pdfsParaFusion[] = $adj;
            }
        }

        // ═══════════════════════════════════════════════════════
        //  GENERAR EL PDF PRINCIPAL DEL CV (con soporte de CHROOT para storage)
        // ═══════════════════════════════════════════════════════
        $pdf = Pdf::loadView('cv.curriculum', [
            'persona'  => $persona,
            'empleado' => $empleado,
            'cargo'    => $cargo,
            'qrCode'   => $qrCode,
            'adjuntos' => $adjuntosParaBlade,
        ]);

        $pdf->setPaper('letter', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        // CRUCIAL: Permitir a DomPDF leer imágenes desde el disco C:/.../storage
        $pdf->setOption('chroot', [public_path(), storage_path()]);

        $cvPdfBinary = $pdf->output();

        // ═══════════════════════════════════════════════════════
        //  FUSIÓN INCREMENTAL DE PDFS (Usando TcpdiDriver para soporte completo de compresión)
        // ═══════════════════════════════════════════════════════
        $accumulatedPdf = $cvPdfBinary;

        foreach ($pdfsParaFusion as $pdfAdj) {
            $pdfPath = $pdfAdj['path'];
            try {
                // TcpdiDriver bypasses FPDI free limitations natively!
                $m = new Merger(new \iio\libmergepdf\Driver\TcpdiDriver);
                $m->addRaw($accumulatedPdf);
                $m->addFile($pdfPath);
                $accumulatedPdf = $m->merge();
                Log::info("✅ PDF Anexado Exitosamente: " . $pdfAdj['label']);
            } catch (\Throwable $e) {
                Log::warning("❌ ERROR AL ANEXAR PDF '{$pdfAdj['label']}': " . $e->getMessage());
                // Si incluso TcpdiDriver falla (raro, a menos que el PDF tenga clave o esté corrupto)
                // lo saltamos sin generar pantallas de fallback intrusivas
            }
        }

        $filename = 'CV_' . str_replace(' ', '_', $persona->primer_apellido . '_' . $persona->nombres) . '.pdf';

        return [
            'pdf_binary' => $accumulatedPdf,
            'filename'   => $filename
        ];
    }
}
