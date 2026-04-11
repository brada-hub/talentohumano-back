<?php

namespace Src\TalentoHumano\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CajaSaludModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EntidadPensionesModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\TipoContratoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CargoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CargoFuncionModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\AreaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\SedeModel;
use Src\Personal\Infrastructure\Persistence\Models\SexoModel;
use Src\Geo\Infrastructure\Persistence\Models\DepartamentoModel;
use Src\Geo\Infrastructure\Persistence\Models\NacionalidadModel;
use Src\Geo\Infrastructure\Persistence\Models\CiudadModel;
use Src\Geo\Infrastructure\Persistence\Models\PaisModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CampusModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\GrupoPersonalModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\NivelJerarquicoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\PuestoModel;

class TalentoHumanoController extends Controller
{
    public function catalogs(): JsonResponse
    {
        return ApiResponse::success([
            'caja_salud'        => CajaSaludModel::all(),
            'entidad_pensiones' => EntidadPensionesModel::all(),
            'tipo_contrato'     => TipoContratoModel::all(),
            'cargos'            => CargoModel::with('funcionesBase')->orderBy('nombre_cargo')->get(),
            'areas'             => AreaModel::orderBy('nombre_area')->get(),
            'sedes'             => SedeModel::all(),
            'sexos'             => SexoModel::all(),
            'departamentos'     => DepartamentoModel::all(),
            'nacionalidades'    => NacionalidadModel::all(),
            'ciudades'          => CiudadModel::all(),
            'paises'            => PaisModel::all(),
            'campus'            => CampusModel::all(),
            'grupos_personal'   => GrupoPersonalModel::orderBy('nombre')->get(),
            'puestos'           => PuestoModel::with(['area', 'cargo', 'grupoPersonal', 'sede'])->orderBy('nombre_puesto')->get(),
        ], 'All catalogs');
    }

    public function getSedes(): JsonResponse
    {
        return ApiResponse::success(SedeModel::with('departamento')->get(), 'Sedes loaded');
    }

    public function storeSede(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre'          => 'required|string|max:255',
            'sigla'           => 'required|string|max:10',
            'id_departamento' => 'nullable|exists:departamentos,id_departamento',
            'activo'          => 'boolean',
        ]);
        
        $sede = SedeModel::create($validated);
        return ApiResponse::success($sede, 'Sede creada correctamente');
    }

    public function updateSede(Request $request, $id): JsonResponse
    {
        $sede = SedeModel::findOrFail($id);
        $validated = $request->validate([
            'nombre'          => 'sometimes|required|string|max:255',
            'sigla'           => 'sometimes|required|string|max:10',
            'id_departamento' => 'nullable|exists:departamentos,id_departamento',
            'activo'          => 'sometimes|boolean',
        ]);
        
        $sede->update($validated);
        return ApiResponse::success($sede, 'Sede actualizada correctamente');
    }

    public function deleteSede($id): JsonResponse
    {
        $sede = SedeModel::findOrFail($id);
        $sede->delete();
        return ApiResponse::success(null, 'Sede eliminada');
    }

    // CAMPUS LOGIC
    public function getCampus(): JsonResponse
    {
        return ApiResponse::success(CampusModel::with('sede')->get(), 'Campus loaded');
    }

    public function storeCampus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre'    => 'required|string|max:255',
            'sigla'     => 'required|string|max:10',
            'id_sede'   => 'required|exists:sedes,id_sede',
            'direccion' => 'nullable|string|max:255',
            'activo'    => 'boolean',
        ]);
        
        $campus = CampusModel::create($validated);
        return ApiResponse::success($campus, 'Campus creado correctamente');
    }

    public function updateCampus(Request $request, $id): JsonResponse
    {
        $campus = CampusModel::findOrFail($id);
        $validated = $request->validate([
            'nombre'    => 'sometimes|required|string|max:255',
            'sigla'     => 'sometimes|required|string|max:10',
            'id_sede'   => 'sometimes|required|exists:sedes,id_sede',
            'direccion' => 'nullable|string|max:255',
            'activo'    => 'sometimes|boolean',
        ]);
        
        $campus->update($validated);
        return ApiResponse::success($campus, 'Campus actualizado correctamente');
    }

    public function deleteCampus($id): JsonResponse
    {
        $campus = CampusModel::findOrFail($id);
        $campus->delete();
        return ApiResponse::success(null, 'Campus eliminado');
    }

    public function getNivelesJerarquicos(): JsonResponse
    {
        return ApiResponse::success(
            NivelJerarquicoModel::orderByDesc('activo')->orderBy('nombre')->get(),
            'Niveles jerarquicos cargados'
        );
    }

    public function storeNivelJerarquico(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'sometimes|boolean',
        ]);

        $nivel = NivelJerarquicoModel::create($validated + [
            'activo' => $validated['activo'] ?? true,
        ]);
        return ApiResponse::success($nivel, 'Nivel jerarquico creado correctamente');
    }

    public function updateNivelJerarquico(Request $request, $id): JsonResponse
    {
        $nivel = NivelJerarquicoModel::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'sometimes|boolean',
        ]);

        $nivel->update($validated);
        return ApiResponse::success($nivel, 'Nivel jerarquico actualizado correctamente');
    }

    public function deleteNivelJerarquico($id): JsonResponse
    {
        $nivel = NivelJerarquicoModel::findOrFail($id);
        $nivel->delete();
        return ApiResponse::success(null, 'Nivel jerarquico eliminado');
    }

    public function getAreas(): JsonResponse
    {
        return ApiResponse::success(
            AreaModel::with('parent')->orderByDesc('activo')->orderBy('nombre_area')->get(),
            'Areas cargadas'
        );
    }

    public function storeArea(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre_area' => 'required|string|max:255',
            'id_area_padre' => 'nullable|exists:areas,id_area',
            'tipo_area' => 'required|string|max:100',
            'activo' => 'sometimes|boolean',
        ]);

        $area = AreaModel::create($validated + [
            'activo' => $validated['activo'] ?? true,
        ]);
        return ApiResponse::success($area->load('parent'), 'Area creada correctamente');
    }

    public function updateArea(Request $request, $id): JsonResponse
    {
        $area = AreaModel::findOrFail($id);
        $validated = $request->validate([
            'nombre_area' => 'sometimes|required|string|max:255',
            'id_area_padre' => 'nullable|exists:areas,id_area',
            'tipo_area' => 'sometimes|required|string|max:100',
            'activo' => 'sometimes|boolean',
        ]);

        $area->update($validated);
        return ApiResponse::success($area->load('parent'), 'Area actualizada correctamente');
    }

    public function deleteArea($id): JsonResponse
    {
        $area = AreaModel::findOrFail($id);
        $area->delete();
        return ApiResponse::success(null, 'Area eliminada');
    }

    public function getCargos(): JsonResponse
    {
        return ApiResponse::success(
            CargoModel::with(['nivelJerarquico', 'funcionesBase'])->orderByDesc('activo')->orderBy('nombre_cargo')->get(),
            'Cargos cargados'
        );
    }

    public function storeCargo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre_cargo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'id_nivel_jerarquico' => 'required|exists:nivel_jerarquico,id_jerarquico',
            'activo' => 'sometimes|boolean',
            'funciones_base' => 'nullable|array',
            'funciones_base.*' => 'nullable|string|max:1000',
        ]);

        $cargo = CargoModel::create([
            'nombre_cargo' => $validated['nombre_cargo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'id_nivel_jerarquico' => $validated['id_nivel_jerarquico'],
            'activo' => $validated['activo'] ?? true,
        ]);

        $this->syncCargoFunciones($cargo, $validated['funciones_base'] ?? []);

        return ApiResponse::success($cargo->load(['nivelJerarquico', 'funcionesBase']), 'Cargo creado correctamente');
    }

    public function updateCargo(Request $request, $id): JsonResponse
    {
        $cargo = CargoModel::findOrFail($id);
        $validated = $request->validate([
            'nombre_cargo' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'id_nivel_jerarquico' => 'sometimes|required|exists:nivel_jerarquico,id_jerarquico',
            'activo' => 'sometimes|boolean',
            'funciones_base' => 'nullable|array',
            'funciones_base.*' => 'nullable|string|max:1000',
        ]);

        $cargo->update(collect($validated)->except('funciones_base')->all());

        if (array_key_exists('funciones_base', $validated)) {
            $this->syncCargoFunciones($cargo, $validated['funciones_base'] ?? []);
        }

        return ApiResponse::success($cargo->load(['nivelJerarquico', 'funcionesBase']), 'Cargo actualizado correctamente');
    }

    public function deleteCargo($id): JsonResponse
    {
        $cargo = CargoModel::findOrFail($id);
        $cargo->delete();
        return ApiResponse::success(null, 'Cargo eliminado');
    }

    public function getGruposPersonal(): JsonResponse
    {
        return ApiResponse::success(
            GrupoPersonalModel::orderByDesc('activo')->orderBy('nombre')->get(),
            'Grupos de personal cargados'
        );
    }

    public function storeGrupoPersonal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:grupos_personal,nombre',
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'sometimes|boolean',
        ]);

        $grupo = GrupoPersonalModel::create($validated + [
            'activo' => $validated['activo'] ?? true,
        ]);

        return ApiResponse::success($grupo, 'Grupo de personal creado correctamente');
    }

    public function updateGrupoPersonal(Request $request, $id): JsonResponse
    {
        $grupo = GrupoPersonalModel::findOrFail($id);
        $validated = $request->validate([
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('grupos_personal', 'nombre')->ignore($grupo->id_grupo_personal, 'id_grupo_personal'),
            ],
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'sometimes|boolean',
        ]);

        $grupo->update($validated);
        return ApiResponse::success($grupo, 'Grupo de personal actualizado correctamente');
    }

    public function deleteGrupoPersonal($id): JsonResponse
    {
        $grupo = GrupoPersonalModel::findOrFail($id);
        $grupo->delete();
        return ApiResponse::success(null, 'Grupo de personal eliminado');
    }

    public function getPuestos(): JsonResponse
    {
        return ApiResponse::success(
            PuestoModel::with([
                'area.parent',
                'cargo.nivelJerarquico',
                'cargo.funcionesBase',
                'grupoPersonal',
                'sede',
                'superiores',
            ])->orderByDesc('activo')->orderBy('nombre_puesto')->get(),
            'Puestos cargados'
        );
    }

    public function storePuesto(Request $request): JsonResponse
    {
        $validated = $this->validatePuesto($request);

        $puesto = PuestoModel::create($this->extractPuestoData($validated));
        $this->syncPuestoSuperiores($puesto, $validated['superiores'] ?? []);

        return ApiResponse::success(
            $puesto->load(['area.parent', 'cargo.nivelJerarquico', 'cargo.funcionesBase', 'grupoPersonal', 'sede', 'superiores']),
            'Puesto creado correctamente'
        );
    }

    public function updatePuesto(Request $request, $id): JsonResponse
    {
        $puesto = PuestoModel::findOrFail($id);
        $validated = $this->validatePuesto($request, $puesto);

        $puesto->update($this->extractPuestoData($validated));
        $this->syncPuestoSuperiores($puesto, $validated['superiores'] ?? []);

        return ApiResponse::success(
            $puesto->load(['area.parent', 'cargo.nivelJerarquico', 'cargo.funcionesBase', 'grupoPersonal', 'sede', 'superiores']),
            'Puesto actualizado correctamente'
        );
    }

    public function deletePuesto($id): JsonResponse
    {
        $puesto = PuestoModel::findOrFail($id);
        $puesto->delete();
        return ApiResponse::success(null, 'Puesto eliminado');
    }

    private function validatePuesto(Request $request, ?PuestoModel $puesto = null): array
    {
        return $request->validate([
            'codigo' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('puestos', 'codigo')->ignore($puesto?->id_puesto, 'id_puesto'),
            ],
            'nombre_puesto' => 'required|string|max:255',
            'id_area' => 'required|exists:areas,id_area',
            'id_cargo' => 'required|exists:cargos,id_cargo',
            'id_grupo_personal' => 'required|exists:grupos_personal,id_grupo_personal',
            'id_sede' => 'nullable|exists:sedes,id_sede',
            'plantilla_contractual' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'sometimes|boolean',
            'superiores' => 'nullable|array',
            'superiores.*' => 'integer|exists:puestos,id_puesto',
        ]);
    }

    private function extractPuestoData(array $validated): array
    {
        return [
            'codigo' => $validated['codigo'] ?? null,
            'nombre_puesto' => $validated['nombre_puesto'],
            'id_area' => $validated['id_area'],
            'id_cargo' => $validated['id_cargo'],
            'id_grupo_personal' => $validated['id_grupo_personal'],
            'id_sede' => $validated['id_sede'] ?? null,
            'plantilla_contractual' => $validated['plantilla_contractual'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'activo' => $validated['activo'] ?? true,
        ];
    }

    private function syncCargoFunciones(CargoModel $cargo, array $funciones): void
    {
        $cargo->funcionesBase()->delete();

        collect($funciones)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->each(function (string $descripcion, int $index) use ($cargo) {
                CargoFuncionModel::create([
                    'id_cargo' => $cargo->id_cargo,
                    'descripcion' => $descripcion,
                    'orden' => $index + 1,
                    'activo' => true,
                ]);
            });
    }

    private function syncPuestoSuperiores(PuestoModel $puesto, array $superiores): void
    {
        $ids = collect($superiores)
            ->map(fn ($item) => (int) $item)
            ->filter(fn (int $id) => $id > 0 && $id !== (int) $puesto->id_puesto)
            ->unique()
            ->values()
            ->all();

        $puesto->superiores()->sync(
            collect($ids)->mapWithKeys(fn (int $id) => [$id => ['tipo_relacion' => 'Inmediato']])->all()
        );
    }
}
