<?php

namespace Src\Beneficios\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Beneficios\Infrastructure\Persistence\Models\ParentescoModel;
use Src\Shared\Infrastructure\Http\ApiResponse;

class BeneficiosController extends Controller
{
    public function catalogs(): JsonResponse
    {
        return ApiResponse::success([
            'parentescos' => ParentescoModel::all(),
        ], 'Catalogs for Beneficios');
    }
}
