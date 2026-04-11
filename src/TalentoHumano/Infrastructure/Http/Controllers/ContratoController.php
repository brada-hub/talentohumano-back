<?php

namespace Src\TalentoHumano\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\TalentoHumano\Application\Contratos\CreateContratoHandler;
use Src\TalentoHumano\Application\Contratos\FinalizeContratoHandler;
use Src\TalentoHumano\Application\Contratos\GenerateIndefinidoPreviewHandler;
use Src\TalentoHumano\Application\Contratos\GeneratePlazoFijoPreviewHandler;
use Src\TalentoHumano\Application\Contratos\GetContratoVersionesHandler;
use Src\TalentoHumano\Application\Contratos\UpdateContratoHandler;
use Src\TalentoHumano\Application\Contratos\UploadContratoFirmadoHandler;
use Src\TalentoHumano\Infrastructure\Http\Requests\CreateContratoRequest;
use Src\TalentoHumano\Infrastructure\Http\Requests\FinalizeContratoRequest;
use Src\TalentoHumano\Infrastructure\Http\Requests\UpdateContratoRequest;
use Src\TalentoHumano\Infrastructure\Http\Requests\UploadContratoFirmadoRequest;

final class ContratoController extends Controller
{
    public function __construct(
        private readonly GeneratePlazoFijoPreviewHandler $previewHandler,
        private readonly GenerateIndefinidoPreviewHandler $previewIndefinidoHandler,
        private readonly CreateContratoHandler $createContratoHandler,
        private readonly UpdateContratoHandler $updateContratoHandler,
        private readonly FinalizeContratoHandler $finalizeContratoHandler,
        private readonly GetContratoVersionesHandler $getContratoVersionesHandler,
        private readonly UploadContratoFirmadoHandler $uploadContratoFirmadoHandler
    ) {
    }

    public function store(CreateContratoRequest $request, int $id): JsonResponse
    {
        try {
            $contrato = $this->createContratoHandler->handle($id, $request->validated());
            return ApiResponse::created($contrato, 'Contrato registrado correctamente');
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        } catch (\Throwable $exception) {
            return ApiResponse::error('No se pudo registrar el contrato: ' . $exception->getMessage(), 500);
        }
    }

    public function update(UpdateContratoRequest $request, int $id, int $contratoId): JsonResponse
    {
        try {
            $contrato = $this->updateContratoHandler->handle($id, $contratoId, $request->validated());
            return ApiResponse::success($contrato, 'Contrato actualizado correctamente');
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        } catch (\Throwable $exception) {
            return ApiResponse::error('No se pudo actualizar el contrato: ' . $exception->getMessage(), 500);
        }
    }

    public function finalize(FinalizeContratoRequest $request, int $id, int $contratoId): JsonResponse
    {
        try {
            $contrato = $this->finalizeContratoHandler->handle(
                $id,
                $contratoId,
                $request->validated('fecha_fin')
            );

            return ApiResponse::success($contrato, 'Contrato finalizado correctamente');
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 422);
        } catch (\Throwable $exception) {
            return ApiResponse::error('No se pudo finalizar el contrato: ' . $exception->getMessage(), 500);
        }
    }

    public function versions(int $id, int $contratoId): JsonResponse
    {
        try {
            $items = $this->getContratoVersionesHandler->handle($id, $contratoId);
            return ApiResponse::success($items, 'Versiones del contrato cargadas correctamente');
        } catch (\Throwable $exception) {
            return ApiResponse::error('No se pudieron cargar las versiones del contrato: ' . $exception->getMessage(), 500);
        }
    }

    public function uploadSigned(UploadContratoFirmadoRequest $request, int $id, int $contratoId): JsonResponse
    {
        try {
            $item = $this->uploadContratoFirmadoHandler->handle($id, $contratoId, [
                'file' => $request->file('file'),
            ]);

            return ApiResponse::created($item, 'Contrato firmado subido correctamente');
        } catch (\Throwable $exception) {
            return ApiResponse::error('No se pudo subir el contrato firmado: ' . $exception->getMessage(), 500);
        }
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

    public function previewIndefinido(Request $request, int $id): JsonResponse
    {
        try {
            $overrides = $request->input('overrides', []);
            $contratoId = $request->integer('id_contrato') ?: null;

            $result = $this->previewIndefinidoHandler->handle($id, is_array($overrides) ? $overrides : [], $contratoId);

            return ApiResponse::success($result, 'Vista previa de contrato indefinido generada correctamente');
        } catch (InvalidArgumentException $exception) {
            return ApiResponse::error($exception->getMessage(), 404);
        } catch (\Throwable $exception) {
            return ApiResponse::error('No se pudo generar la vista previa del contrato indefinido: ' . $exception->getMessage(), 500);
        }
    }

    public function descargarIndefinido(Request $request, int $id): Response
    {
        try {
            $overrides = $request->input('overrides', []);
            $contratoId = $request->integer('id_contrato') ?: null;

            $result = $this->previewIndefinidoHandler->renderPdf($id, is_array($overrides) ? $overrides : [], $contratoId);

            return response($result['pdf_binary'])
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"');
        } catch (InvalidArgumentException $exception) {
            abort(404, $exception->getMessage());
        } catch (\Throwable $exception) {
            abort(500, 'No se pudo generar el contrato indefinido: ' . $exception->getMessage());
        }
    }
}
