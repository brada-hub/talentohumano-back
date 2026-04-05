<?php

namespace Src\TalentoHumano\Application\Empleados;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use setasign\Fpdi\Tcpdf\Fpdi;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;

final class GenerateCvPdfHandler
{
    private const OFICIO_WIDTH_MM = 216.0;
    private const OFICIO_HEIGHT_MM = 330.0;
    private const OFICIO_WIDTH_PT = 612.2834645669;
    private const OFICIO_HEIGHT_PT = 935.4330708661;
    private const CONTENT_MARGIN_MM = 10.0;

    public function __construct(
        private readonly EmpleadoRepositoryInterface $repo
    ) {}

    /**
     * @return array{pdf_binary:string,filename:string}
     */
    public function handle(string $personaId): array
    {
        $details = $this->repo->findPersonaCvDetails($personaId);

        if (!$details) {
            throw new InvalidArgumentException('Persona no encontrada');
        }

        $persona = $details['persona'];
        $empleado = $details['empleado'];

        $cargo = null;
        if ($empleado && $empleado->contratoActivo && $empleado->contratoActivo->cargo) {
            $cargo = $empleado->contratoActivo->cargo->nombre_cargo;
        }

        try {
            $qrCode = QrCode::format('svg')
                ->size(120)
                ->margin(1)
                ->errorCorrection('H')
                ->generate(config('app.url') . '/api/v1/cv/verificar/' . $persona->id);
        } catch (\Throwable $e) {
            Log::warning('No se pudo generar QR con SVG: ' . $e->getMessage());
            $qrCode = '';
        }

        $adjuntosValidos = [];
        foreach ($this->repo->getAttachments($details) as $adjunto) {
            $path = $adjunto['path'] ?? null;
            if (!$path || !file_exists($path)) {
                Log::info('Adjunto no encontrado en disco: ' . ($path ?? 'null') . ' - ' . ($adjunto['label'] ?? 'Sin etiqueta'));
                continue;
            }

            $adjuntosValidos[] = $adjunto;
        }

        $pdf = Pdf::loadView('cv.curriculum', [
            'persona' => $persona,
            'empleado' => $empleado,
            'cargo' => $cargo,
            'qrCode' => $qrCode,
            'adjuntos' => [],
        ]);

        $pdf->setPaper([0, 0, self::OFICIO_WIDTH_PT, self::OFICIO_HEIGHT_PT]);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('chroot', [public_path(), storage_path()]);

        $cvPdfBinary = $pdf->output();
        $finalPdfBinary = $this->composeOficioPdf($cvPdfBinary, $adjuntosValidos);

        $filename = 'CV_' . str_replace(' ', '_', $persona->primer_apellido . '_' . $persona->nombres) . '.pdf';

        return [
            'pdf_binary' => $finalPdfBinary,
            'filename' => $filename,
        ];
    }

    private function composeOficioPdf(string $cvPdfBinary, array $adjuntos): string
    {
        $pdf = new Fpdi('P', 'mm', [self::OFICIO_WIDTH_MM, self::OFICIO_HEIGHT_MM], true, 'UTF-8', false);
        $pdf->SetCreator('SIGETH');
        $pdf->SetAuthor('SIGETH');
        $pdf->SetTitle('Curriculum Vitae');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetCompression(true);
        $pdf->setImageScale(1);

        $tempMainPdf = tempnam(sys_get_temp_dir(), 'sigeth_cv_');
        if ($tempMainPdf === false) {
            throw new InvalidArgumentException('No se pudo preparar el archivo temporal del CV');
        }

        file_put_contents($tempMainPdf, $cvPdfBinary);

        try {
            $this->appendPdfToOficioDocument($pdf, $tempMainPdf);

            foreach ($adjuntos as $adjunto) {
                try {
                    if (($adjunto['type'] ?? '') === 'image') {
                        $this->appendImageToOficioDocument($pdf, $adjunto['path']);
                    } else {
                        $this->appendPdfToOficioDocument($pdf, $adjunto['path']);
                    }
                } catch (\Throwable $e) {
                    Log::warning("No se pudo anexar '{$adjunto['label']}' al CV: " . $e->getMessage());
                }
            }

            return $pdf->Output('', 'S');
        } finally {
            @unlink($tempMainPdf);
        }
    }

    private function appendPdfToOficioDocument(Fpdi $pdf, string $pdfPath): void
    {
        $pageCount = $pdf->setSourceFile($pdfPath);

        for ($page = 1; $page <= $pageCount; $page++) {
            $template = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($template);

            $pdf->AddPage('P', [self::OFICIO_WIDTH_MM, self::OFICIO_HEIGHT_MM]);

            [$x, $y, $width, $height] = $this->fitInsideOficio(
                (float) $size['width'],
                (float) $size['height']
            );

            $pdf->useTemplate($template, $x, $y, $width, $height);
        }
    }

    private function appendImageToOficioDocument(Fpdi $pdf, string $imagePath): void
    {
        $imageSize = @getimagesize($imagePath);
        if ($imageSize === false) {
            throw new InvalidArgumentException("No se pudo leer la imagen: {$imagePath}");
        }

        [$sourceWidth, $sourceHeight] = $imageSize;

        $pdf->AddPage('P', [self::OFICIO_WIDTH_MM, self::OFICIO_HEIGHT_MM]);

        [$x, $y, $width, $height] = $this->fitInsideOficio(
            (float) $sourceWidth,
            (float) $sourceHeight
        );

        $pdf->Image(
            $imagePath,
            $x,
            $y,
            $width,
            $height,
            '',
            '',
            '',
            false,
            300,
            '',
            false,
            false,
            0,
            false,
            false,
            false
        );
    }

    private function fitInsideOficio(float $sourceWidth, float $sourceHeight): array
    {
        $maxWidth = self::OFICIO_WIDTH_MM - (self::CONTENT_MARGIN_MM * 2);
        $maxHeight = self::OFICIO_HEIGHT_MM - (self::CONTENT_MARGIN_MM * 2);

        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            return [self::CONTENT_MARGIN_MM, self::CONTENT_MARGIN_MM, $maxWidth, $maxHeight];
        }

        $scale = min($maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $width = $sourceWidth * $scale;
        $height = $sourceHeight * $scale;
        $x = (self::OFICIO_WIDTH_MM - $width) / 2;
        $y = (self::OFICIO_HEIGHT_MM - $height) / 2;

        return [$x, $y, $width, $height];
    }
}
