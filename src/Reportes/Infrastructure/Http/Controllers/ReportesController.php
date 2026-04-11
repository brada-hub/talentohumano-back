<?php

namespace Src\Reportes\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Src\Reportes\Application\ExportReportesHandler;
use Src\Reportes\Application\GetDashboardReportesHandler;
use Src\Shared\Infrastructure\Http\ApiResponse;

final class ReportesController extends Controller
{
    public function __construct(
        private readonly GetDashboardReportesHandler $handler,
        private readonly ExportReportesHandler $exportHandler
    ) {
    }

    public function dashboard(Request $request): JsonResponse
    {
        $filters = [
            'mes' => $request->integer('mes') ?: null,
            'id_sede' => $request->integer('id_sede') ?: null,
            'id_area' => $request->integer('id_area') ?: null,
        ];

        $data = $this->handler->handle($filters);

        return ApiResponse::success($data, 'Reportes cargados correctamente');
    }

    public function export(Request $request): Response
    {
        try {
            $filters = [
                'mes' => $request->integer('mes') ?: null,
                'id_sede' => $request->integer('id_sede') ?: null,
                'id_area' => $request->integer('id_area') ?: null,
            ];

            $section = (string) $request->query('seccion', 'sedes');
            $format = (string) $request->query('formato', 'excel');

            $result = $this->exportHandler->handle($filters, $section, $format);

            return response($result['binary'])
                ->header('Content-Type', $result['content_type'])
                ->header('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"');
        } catch (InvalidArgumentException $exception) {
            abort(422, $exception->getMessage());
        }
    }
}
