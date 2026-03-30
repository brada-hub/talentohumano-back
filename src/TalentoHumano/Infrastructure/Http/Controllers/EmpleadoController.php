<?php

namespace Src\TalentoHumano\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\TalentoHumano\Application\Empleados\GetEmpleadosHandler;
use Src\TalentoHumano\Application\Empleados\GetEmpleadoDetailsHandler;
use Src\TalentoHumano\Application\Empleados\SearchPersonaHandler;
use Src\TalentoHumano\Application\Empleados\CreateEmpleadoHandler;
use Src\TalentoHumano\Application\Empleados\UpdateEmpleadoHandler;
use Src\TalentoHumano\Application\Stats\GetEmpleadoStatsHandler;
use InvalidArgumentException;

class EmpleadoController extends Controller
{
    public function __construct(
        private readonly GetEmpleadosHandler $getEmpleadosHandler,
        private readonly GetEmpleadoDetailsHandler $getEmpleadoDetailsHandler,
        private readonly SearchPersonaHandler $searchPersonaHandler,
        private readonly CreateEmpleadoHandler $createEmpleadoHandler,
        private readonly UpdateEmpleadoHandler $updateEmpleadoHandler,
        private readonly GetEmpleadoStatsHandler $getEmpleadoStatsHandler
    ) {}

    public function index(): JsonResponse
    {
        $employees = $this->getEmpleadosHandler->handle();
        return ApiResponse::success($employees, 'Empleados listados correctamente');
    }

    public function show($id): JsonResponse
    {
        try {
            $employee = $this->getEmpleadoDetailsHandler->handle((int)$id);
            return ApiResponse::success($employee, 'Detalles de empleado con CV Normalizado');
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    public function buscarPersona(Request $request): JsonResponse
    {
        $ci = $request->query('ci');
        if (!$ci) return ApiResponse::error('Cédula de identidad requerida', 400);

        try {
            $persona = $this->searchPersonaHandler->handle($ci);
            return ApiResponse::success($persona, 'Persona encontrada');
        } catch (InvalidArgumentException $e) {
            $status = $e->getMessage() === 'Persona no encontrada' ? 404 : 422;
            return ApiResponse::error($e->getMessage(), $status);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // Paso 1: Persona
            'primer_apellido'     => 'required|string|max:100',
            'segundo_apellido'    => 'nullable|string|max:100',
            'nombres'             => 'required|string|max:100',
            'ci'                  => 'required|string|max:15',
            'id_ci_expedido'      => 'required|integer',
            'id_sexo'             => 'required|integer',
            'celular_personal'    => 'required|string|max:15',
            'correo_personal'     => 'required|email',
            'estado_civil'        => 'required|string',
            'id_nacionalidad'     => 'required|integer',
            'direccion_domicilio' => 'required|string|max:255',
            'id_ciudad'           => 'required|integer',
            'id_pais'             => 'required|integer',

            // Paso 2: Contrato (Opcional)
            'id_tipo_contrato'    => 'nullable|integer',
            'id_area'             => 'nullable|integer',
            'id_cargo'            => 'nullable|integer',
            
            // Paso 3: Seguridad Social
            'id_caja'               => 'nullable|integer',
            'id_entidad_pensiones'  => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Datos inválidos', 422, $validator->errors());
        }

        try {
            $personaData = $request->only([
                'primer_apellido', 'segundo_apellido', 'nombres', 'ci', 'complemento',
                'id_ci_expedido', 'id_sexo', 'celular_personal', 'correo_personal',
                'estado_civil', 'id_nacionalidad', 'direccion_domicilio', 'id_ciudad', 'id_pais'
            ]);

            $empleadoData = $request->only([
                'celular_institucional', 'correo_institucional', 'id_caja',
                'nro_matricula_seguro', 'id_entidad_pensiones', 'nro_nua_cua'
            ]);

            $contratoData = null;
            if ($request->filled('id_tipo_contrato') && $request->filled('id_area') && $request->filled('id_cargo')) {
                $contratoData = $request->only([
                    'id_tipo_contrato', 'id_area', 'id_cargo', 'salario',
                    'fecha_inicio', 'fecha_fin', 'id_sede'
                ]);
            }

            $result = $this->createEmpleadoHandler->handle($personaData, $empleadoData, $contratoData);

            return ApiResponse::created($result, 'Empleado registrado exitosamente');

        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al registrar: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $result = $this->updateEmpleadoHandler->handle((int)$id, $request->all());
            return ApiResponse::success($result, 'Empleado actualizado correctamente');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar: ' . $e->getMessage(), 500);
        }
    }

    public function stats(): JsonResponse
    {
        $stats = $this->getEmpleadoStatsHandler->handle();
        return ApiResponse::success($stats, 'Dashboard statistics');
    }
}
