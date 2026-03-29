<?php

namespace Src\TalentoHumano\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CajaSaludModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EntidadPensionesModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\TipoContratoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CargoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\AreaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\SedeModel;
use Src\Personal\Infrastructure\Persistence\Models\SexoModel;
use Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel;
use Src\Geo\Infrastructure\Persistence\Models\NacionalidadModel;
use Src\Geo\Infrastructure\Persistence\Models\CiudadModel;
use Src\Geo\Infrastructure\Persistence\Models\PaisModel;

class TalentoHumanoController extends Controller
{
    public function catalogs(): JsonResponse
    {
        return ApiResponse::success([
            'caja_salud'        => CajaSaludModel::all(),
            'entidad_pensiones' => EntidadPensionesModel::all(),
            'tipo_contrato'     => TipoContratoModel::all(),
            'cargos'            => CargoModel::all(),
            'areas'             => AreaModel::all(),
            'sedes'             => SedeModel::all(),
            'sexos'             => SexoModel::all(),
            'departamentos'     => DepartamentoModel::all(),
            'nacionalidades'    => NacionalidadModel::all(),
            'ciudades'          => CiudadModel::all(),
            'paises'            => PaisModel::all(),
        ], 'All catalogs');
    }
}
