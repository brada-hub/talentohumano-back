<?php

namespace Src\Geo\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Geo\Application\List\ListPaisesHandler;
use Src\Geo\Application\List\ListDepartamentosHandler;
use Src\Geo\Application\List\ListCiudadesHandler;
use Src\Geo\Application\List\ListNacionalidadesHandler;
use Src\Shared\Infrastructure\Http\ApiResponse;

class GeoController extends Controller
{
    public function __construct(
        private readonly ListPaisesHandler $listPaisesHandler,
        private readonly ListDepartamentosHandler $listDepartamentosHandler,
        private readonly ListCiudadesHandler $listCiudadesHandler,
        private readonly ListNacionalidadesHandler $listNacionalidadesHandler
    ) {}

    public function paises(): JsonResponse
    {
        return ApiResponse::success($this->listPaisesHandler->handle());
    }

    public function departamentos(int $paisId): JsonResponse
    {
        return ApiResponse::success($this->listDepartamentosHandler->handle($paisId));
    }

    public function ciudades(int $departamentoId): JsonResponse
    {
        return ApiResponse::success($this->listCiudadesHandler->handle($departamentoId));
    }

    public function nacionalidades(): JsonResponse
    {
        return ApiResponse::success($this->listNacionalidadesHandler->handle());
    }

    public function searchCiudades(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = $request->query('search');
        if (!$query || strlen($query) < 2) {
            return ApiResponse::success([]);
        }

        $results = \Src\Geo\Infrastructure\Persistence\Models\CiudadModel::with('departamento')
            ->where('nombre', 'LIKE', "%{$query}%")
            ->orderBy('nombre', 'asc')
            ->limit(20)
            ->get();

        return ApiResponse::success($results);
    }
}
