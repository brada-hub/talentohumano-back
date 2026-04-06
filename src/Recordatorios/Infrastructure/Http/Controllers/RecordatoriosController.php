<?php

namespace Src\Recordatorios\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Recordatorios\Application\SendCumpleaniosRecordatorioHandler;
use Src\Recordatorios\Application\GetResumenRecordatoriosHandler;
use Src\Shared\Infrastructure\Http\ApiResponse;

final class RecordatoriosController extends Controller
{
    public function __construct(
        private readonly GetResumenRecordatoriosHandler $getResumenHandler,
        private readonly SendCumpleaniosRecordatorioHandler $sendCumpleaniosHandler
    ) {
    }

    public function resumen(): JsonResponse
    {
        $filters = request()->only(['mes', 'id_sede', 'id_area']);

        return ApiResponse::success(
            $this->getResumenHandler->handle($filters),
            'Resumen de recordatorios obtenido correctamente'
        );
    }

    public function enviarCumpleanios(Request $request, int $empleadoId): JsonResponse
    {
        $data = $this->sendCumpleaniosHandler->handle(
            $empleadoId,
            (bool) $request->boolean('automatico', false),
            (bool) $request->boolean('force', false)
        );

        return ApiResponse::success($data, $data['message'] ?? 'Recordatorio procesado');
    }
}
