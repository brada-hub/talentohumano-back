<?php

namespace Src\TalentoHumano\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\Personal\Infrastructure\Persistence\Models\PersonaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EmpleadoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ContratoModel;

class EmpleadoController extends Controller
{
    public function index(): JsonResponse
    {
        $employees = EmpleadoModel::with([
            'persona',
            'persona.sexo',
            'contratoActivo',
            'contratoActivo.cargo',
            'contratoActivo.area',
            'contratoActivo.sede',
        ])
        ->where('estado_laboral', 'Activo')
        ->get();

        return ApiResponse::success($employees, 'Employees listed successfully');
    }

    public function show($id): JsonResponse
    {
        $employee = EmpleadoModel::with([
            'persona',
            'persona.sexo',
            'persona.nacionalidad',
            'persona.ciudad',
            'persona.ciudad.departamento',
            'persona.expedido',
            'caja',
            'pensiones',
            'contratoActivo.cargo',
            'contratoActivo.area',
            'contratoActivo.sede',
            'contratos.tipo',
            'contratos.area',
            'contratos.cargo',
            'contratos.sede',
            // CV Normalizado (Secciones Académicas)
            'persona.formacionPregrado.depto',
            'persona.formacionPostgrado.depto',
            'persona.experienciaDocente.depto',
            'persona.experienciaProfesional.depto',
            'persona.capacitaciones.depto',
            'persona.produccionIntelectual.depto',
            'persona.reconocimientos',
            'persona.idiomas',
        ])->find($id);

        if (!$employee) {
            return ApiResponse::error('Employee not found', 404);
        }

        return ApiResponse::success($employee, 'Employee details with Normalized CV');
    }

    public function buscarPersona(Request $request): JsonResponse
    {
        $ci = $request->query('ci');
        if (!$ci) return ApiResponse::error('Cédula de identidad requerida', 400);

        $persona = PersonaModel::where('ci', $ci)->first();
        if (!$persona) return ApiResponse::error('Persona no encontrada', 404);

        $isEmpleado = EmpleadoModel::where('id_persona', $persona->id)->exists();
        if ($isEmpleado) return ApiResponse::error('Ya es un Empleado', 422);

        return ApiResponse::success($persona, 'Persona encontrada');
    }

    /**
     * Registrar un nuevo empleado con persona y contrato en una sola transacción.
     */
    public function store(Request $request): JsonResponse
    {
        $existingPersona = PersonaModel::where('ci', $request->ci)->first();
        $personaId = $existingPersona ? $existingPersona->id : null;

        if ($personaId) {
            $isEmpleado = EmpleadoModel::where('id_persona', $personaId)->exists();
            if ($isEmpleado) {
                return ApiResponse::error('Ya existe un empleado con esta CI.', 422);
            }
        }

        $validator = Validator::make($request->all(), [
            // Paso 1: Persona
            'primer_apellido'     => 'required|string|max:100',
            'segundo_apellido'    => 'nullable|string|max:100',
            'nombres'             => 'required|string|max:100',
            'ci'                  => 'required|string|max:15' . ($personaId ? '' : '|unique:personas,ci'),
            'complemento'         => 'nullable|string|max:5',
            'id_ci_expedido'      => 'required|exists:departamentos,id_departamento',
            'id_sexo'             => 'required|exists:sexo,id_sexo',
            'celular_personal'    => 'required|string|max:15',
            'correo_personal'     => 'required|email|unique:personas,correo_personal' . ($personaId ? ',' . $personaId . ',id' : ''),
            'estado_civil'        => 'required|string',
            'id_nacionalidad'     => 'required|exists:nacionalidades,id_nacionalidad',
            'direccion_domicilio' => 'required|string|max:255',
            'id_ciudad'           => 'required|exists:ciudades,id_ciudad',
            'id_pais'             => 'required|exists:paises,id_pais',

            // Paso 2: Contrato (Opcional)
            'id_tipo_contrato'    => 'nullable|exists:tipo_contrato,id_tipo_contrato',
            'id_area'             => 'nullable|exists:areas,id_area',
            'id_cargo'            => 'nullable|exists:cargos,id_cargo',
            'salario'             => 'nullable|numeric|min:0',
            'fecha_inicio'        => 'nullable|date',
            'fecha_fin'           => 'nullable|date|after:fecha_inicio',
            'id_sede'             => 'nullable|exists:sedes,id_sede',

            // Paso 3: Seguridad Social (Opcional)
            'id_caja'               => 'nullable|exists:caja_salud,id_caja',
            'id_entidad_pensiones'  => 'nullable|exists:entidad_pensiones,id_entidad_pensiones',
            'nro_matricula_seguro'  => 'nullable|string',
            'nro_nua_cua'           => 'nullable|string',
            'celular_institucional' => 'nullable|string|max:15',
            'correo_institucional'  => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Datos inválidos', 422, $validator->errors());
        }

        try {
            $result = DB::transaction(function () use ($request, $existingPersona) {
                // 1. Crear o Actualizar Persona
                $pData = [
                    'primer_apellido'     => $request->primer_apellido,
                    'segundo_apellido'    => $request->segundo_apellido,
                    'nombres'             => $request->nombres,
                    'ci'                  => $request->ci,
                    'complemento'         => $request->complemento,
                    'id_ci_expedido'      => $request->id_ci_expedido,
                    'id_sexo'             => $request->id_sexo,
                    'celular_personal'    => $request->celular_personal,
                    'correo_personal'     => $request->correo_personal,
                    'estado_civil'        => $request->estado_civil,
                    'id_nacionalidad'     => $request->id_nacionalidad,
                    'direccion_domicilio' => $request->direccion_domicilio,
                    'id_ciudad'           => $request->id_ciudad,
                    'id_pais'             => $request->id_pais,
                    'activo'              => true,
                ];

                if ($existingPersona) {
                    $existingPersona->update($pData);
                    $persona = $existingPersona;
                } else {
                    $persona = PersonaModel::create($pData);
                }

                // 2. Crear Empleado
                $empleado = EmpleadoModel::create([
                    'id_persona'            => $persona->id,
                    'celular_institucional'  => $request->celular_institucional,
                    'correo_institucional'   => $request->correo_institucional,
                    'id_caja'               => $request->id_caja,
                    'nro_matricula_seguro'  => $request->nro_matricula_seguro,
                    'id_entidad_pensiones'  => $request->id_entidad_pensiones,
                    'nro_nua_cua'           => $request->nro_nua_cua,
                    'estado_laboral'        => 'Activo',
                ]);

                // 3. Crear Contrato
                if ($request->id_tipo_contrato && $request->id_area && $request->id_cargo) {
                    ContratoModel::create([
                        'id_empleado'      => $empleado->id_empleado,
                        'id_tipo_contrato' => $request->id_tipo_contrato,
                        'id_area'          => $request->id_area,
                        'id_cargo'         => $request->id_cargo,
                        'salario'          => $request->salario,
                        'fecha_inicio'     => $request->fecha_inicio,
                        'fecha_fin'        => $request->fecha_fin,
                        'id_sede'          => $request->id_sede,
                        'estado_contrato'  => 'Activo',
                    ]);
                }

                return $empleado->load(['persona', 'contratoActivo.cargo', 'contratoActivo.area']);
            });

            return ApiResponse::created($result, 'Empleado registrado exitosamente');

        } catch (\Exception $e) {
            return ApiResponse::error('Error al registrar: ' . $e->getMessage(), 500);
        }
    }
    public function stats(): JsonResponse
    {
        $total = EmpleadoModel::where('estado_laboral', 'Activo')->count();

        // Por género
        $genderStats = DB::table('empleados')
            ->join('personas', 'empleados.id_persona', '=', 'personas.id')
            ->join('sexo', 'personas.id_sexo', '=', 'sexo.id_sexo')
            ->select('sexo.sexo as label', DB::raw('count(*) as value'))
            ->where('empleados.estado_laboral', 'Activo')
            ->groupBy('sexo.sexo')
            ->get();

        // Por Sede
        $sedeStats = DB::table('contratos')
            ->join('sedes', 'contratos.id_sede', '=', 'sedes.id_sede')
            ->select('sedes.nombre as label', DB::raw('count(*) as value'))
            ->where('contratos.estado_contrato', 'Activo')
            ->groupBy('sedes.nombre')
            ->get();

        // Por Área
        $areaStats = DB::table('contratos')
            ->join('areas', 'contratos.id_area', '=', 'areas.id_area')
            ->select('areas.nombre_area as label', DB::raw('count(*) as value'))
            ->where('contratos.estado_contrato', 'Activo')
            ->groupBy('areas.nombre_area')
            ->orderBy('value', 'desc')
            ->limit(5)
            ->get();

        return ApiResponse::success([
            'total_active' => $total,
            'genders'      => $genderStats,
            'sedes'        => $sedeStats,
            'areas'        => $areaStats,
            'recent'       => EmpleadoModel::with(['persona', 'contratoActivo.cargo'])->latest()->limit(5)->get()
        ], 'Dashboard statistics');
    }
}
