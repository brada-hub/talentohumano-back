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
        // Notice: We couple tightly to config('app.url') as this is a Laravel Application bounded concern.
        $qrCode = QrCode::format('svg')
            ->size(120)
            ->errorCorrection('H')
            ->generate(config('app.url') . '/api/v1/cv/verificar/' . $persona->id);

        $adjuntosRaw = $this->repo->getAttachments($details);
        $adjuntosValidados = [];
        $pdfsParaFusion = [];

        foreach ($adjuntosRaw as $adj) {
            if ($adj['type'] === 'image') {
                $adjuntosValidados[] = $adj;
            } elseif ($adj['type'] === 'pdf' && !empty($adj['path']) && file_exists($adj['path'])) {
                try {
                    $fpdi = new \setasign\Fpdi\Tcpdf\Fpdi();
                    $fpdi->setSourceFile($adj['path']);
                    $pdfsParaFusion[] = $adj['path'];
                } catch (\Throwable $e) {
                    $adjuntosValidados[] = $adj;
                }
            }
        }

        $pdf = Pdf::loadView('cv.curriculum', [
            'persona'  => $persona,
            'empleado' => $empleado,
            'cargo'    => $cargo,
            'qrCode'   => $qrCode,
            'adjuntos' => $adjuntosValidados,
        ]);

        $pdf->setPaper('letter', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);

        $merger = new Merger;
        $merger->addRaw($pdf->output());

        foreach ($pdfsParaFusion as $pdfPath) {
            try { $merger->addFile($pdfPath); } catch (\Throwable $e) {}
        }

        try {
            $finalPdf = $merger->merge();
        } catch (\Throwable $e) {
            Log::error("Merge final falló: " . $e->getMessage());
            $finalPdf = $pdf->output(); // Fallback si algo catastrofico pasa con fpdi/merge
        }
        
        $filename = 'CV_' . str_replace(' ', '_', $persona->primer_apellido . '_' . $persona->nombres) . '.pdf';

        return [
            'pdf_binary' => $finalPdf,
            'filename'   => $filename
        ];
    }
}
