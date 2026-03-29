<?php

namespace Src\TalentoHumano\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Src\TalentoHumano\Application\Empleados\GenerateCvPdfHandler;
use Src\TalentoHumano\Application\Empleados\VerifyCvQrHandler;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class CvController extends Controller
{
    public function __construct(
        private readonly GenerateCvPdfHandler $generateCvHandler,
        private readonly VerifyCvQrHandler $verifyQrHandler
    ) {}

    /**
     * Genera y descarga el CV completo en PDF
     */
    public function descargar(string $personaId): Response
    {
        try {
            $result = $this->generateCvHandler->handle($personaId);

            return response($result['pdf_binary'])
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"');
        } catch (\InvalidArgumentException $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Vista previa del CV en el navegador
     */
    public function preview(string $personaId): Response
    {
        try {
            $result = $this->generateCvHandler->handle($personaId);

            return response($result['pdf_binary'])
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="CV_Preview.pdf"');
        } catch (\InvalidArgumentException $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Endpoint de verificación QR
     */
    public function verificar(string $personaId): JsonResponse
    {
        try {
            $payload = $this->verifyQrHandler->handle($personaId);
            return response()->json($payload);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['valido' => false, 'message' => $e->getMessage()], 404);
        }
    }
}
