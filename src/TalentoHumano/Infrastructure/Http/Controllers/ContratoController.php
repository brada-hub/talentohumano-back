<?php

namespace Src\TalentoHumano\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\TalentoHumano\Application\Contratos\GeneratePlazoFijoPreviewHandler;

final class ContratoController extends Controller
{
    public function __construct(
        private readonly GeneratePlazoFijoPreviewHandler $previewHandler
    ) {
    }

    public function previewPlazoFijo(Request $request, int $id): JsonResponse
    {
        try {
            $overrides = $request->input('overrides', []);
            $contratoId = $request->integer('id_contrato') ?: null;

            $result = $this->previewHandler->handle($id, is_array($overrides) ? $overrides : [], $contratoId);

            return ApiResponse::success($result, 'Vista previa de contrato generada correctamente');
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 404);
        } catch (\Throwable $exception) {
            return ApiResponse::error('No se pudo generar la vista previa del contrato: ' . $exception->getMessage(), 500);
        }
    }

    public function descargarPlazoFijo(Request $request, int $id): Response
    {
        try {
            $overrides = $request->input('overrides', []);
            $contratoId = $request->integer('id_contrato') ?: null;

            $result = $this->previewHandler->renderPdf($id, is_array($overrides) ? $overrides : [], $contratoId);

            return response($result['pdf_binary'])
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"');
        } catch (InvalidArgumentException $exception) {
            abort(404, $exception->getMessage());
        } catch (\Throwable $exception) {
            abort(500, 'No se pudo generar el contrato: ' . $exception->getMessage());
        }
    }
}
