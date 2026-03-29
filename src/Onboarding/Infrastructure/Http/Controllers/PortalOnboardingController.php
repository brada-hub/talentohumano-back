<?php

namespace Src\Onboarding\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingTokenModel;
use Src\Personal\Infrastructure\Persistence\Models\PersonaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\FormacionPregradoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\FormacionPostgradoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ExperienciaDocenteModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ExperienciaProfesionalModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CapacitacionModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\IdiomaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ReconocimientoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ProduccionIntelectualModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Src\Personal\Infrastructure\Persistence\Models\DocumentoPersonaModel;

class PortalOnboardingController extends Controller
{
    public function __construct()
    {
        $enabled = DB::table('_configs')
            ->where('key', 'onboarding_enabled')
            ->value('value');

        if ($enabled == '0' || $enabled === null) {
            abort(403, 'El portal de registro se encuentra deshabilitado temporalmente. Contacta a RRHH.');
        }
    }

    public function validarToken($token)
    {
        $otoken = OnboardingTokenModel::where('token', $token)
            ->where('activo', true)
            ->whereNull('usado_en')
            ->first();

        if (!$otoken) {
            return response()->json(['valido' => false, 'message' => 'Token inválido o expirado'], 404);
        }

        return response()->json([
            'valido' => true,
            'id_persona' => $otoken->id_persona,
            'token' => $token
        ]);
    }

    public function verificar(Request $request)
    {
        $request->validate([
            'ci' => 'required',
            'fecha_nacimiento' => 'required|date',
            'token' => 'nullable|string'
        ]);

        $persona = null;

        if ($request->token) {
            $otoken = OnboardingTokenModel::where('token', $request->token)->where('activo', true)->first();
            if ($otoken && $otoken->id_persona) {
                $persona = PersonaModel::with([
                    'sexo', 'nacionalidad', 'ciudad', 'pais', 'expedido', 'documentos',
                    'formacionPregrado', 'formacionPostgrado', 'experienciaDocente',
                    'experienciaProfesional', 'capacitaciones', 'idiomas',
                    'reconocimientos', 'produccionIntelectual'
                ])->find($otoken->id_persona);
            }
        }

        if (!$persona) {
            $persona = PersonaModel::where('ci', $request->ci)->first();

            if ($persona) {
                $fechaDb = $persona->fecha_nacimiento ? Carbon::parse($persona->fecha_nacimiento)->format('Y-m-d') : null;
                $fechaInput = Carbon::parse($request->fecha_nacimiento)->format('Y-m-d');

                if ($fechaDb && $fechaDb !== $fechaInput) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La fecha de nacimiento no coincide con el CI proporcionado.',
                        'datos_precargados' => null,
                    ], 401);
                }

                $persona->load([
                    'sexo', 'nacionalidad', 'ciudad', 'pais', 'expedido', 'documentos',
                    'formacionPregrado', 'formacionPostgrado', 'experienciaDocente',
                    'experienciaProfesional', 'capacitaciones', 'idiomas',
                    'reconocimientos', 'produccionIntelectual'
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'session_key' => $request->token ?: 'new_session_' . Str::random(10),
            'datos_precargados' => $persona,
            'estado' => $persona ? $persona->estado_onboarding : 'sin_iniciar'
        ]);
    }

    public function guardarPersonal(Request $request)
    {
        $data = $request->all();

        $persona = null;
        if ($request->id) {
            $persona = PersonaModel::find($request->id);
        } else {
            $persona = PersonaModel::where('ci', $request->ci)->first() ?: new PersonaModel();
        }

        $expedidoRaw = $data['id_expedido'] ?? $data['id_ci_expedido'] ?? null;
        if ($expedidoRaw) {
            if (!is_numeric($expedidoRaw)) {
                $data['id_ci_expedido'] = DB::table('departamentos')
                    ->where('codigo_expedido', $expedidoRaw)->value('id_departamento') ?? 1;
            } else {
                $data['id_ci_expedido'] = (int) $expedidoRaw;
            }
        }
        unset($data['id_expedido']);

        if (isset($data['nacionalidad']) && !is_numeric($data['nacionalidad'])) {
            $data['id_nacionalidad'] = DB::table('nacionalidades')
                ->where('gentilicio', $data['nacionalidad'])->value('id_nacionalidad') ?? 1;
        }
        unset($data['nacionalidad']);

        if (isset($data['id_ciudad']) && !is_numeric($data['id_ciudad'])) {
            $data['id_ciudad'] = DB::table('ciudades')
                ->where('nombre', 'like', '%' . $data['id_ciudad'] . '%')->value('id_ciudad') ?? 1;
        }

        unset($data['foto'], $data['session_key'], $data['ci_escaneado'], $data['id_depto_residencia']);

        $persona->fill($data);
        $persona->estado_onboarding = 'en_progreso';
        $persona->save();

        return response()->json(['success' => true, 'persona' => $persona]);
    }

    public function guardarAcademico(Request $request)
    {
        $tipo = $request->tipo;
        $data = $request->except('tipo', 'session_key');

        $model = null;
        if ($tipo === 'pregrado') {
            $model = new FormacionPregradoModel();
        } else {
            $model = new FormacionPostgradoModel();
        }

        $model->fill($data);
        $model->save();

        return response()->json(['success' => true, 'id' => $model->id]);
    }

    /**
     * Finaliza el proceso completo de registro
     */
    public function completar(Request $request)
    {
        $dPersona = $request->input('persona');
        $dAcademico = $request->input('academico', []);
        $dExperiencia = $request->input('experiencia', []);
        $dOtros = $request->input('otros', []);

        if (empty($dPersona['ci'])) {
            return ApiResponse::error('CI requerido');
        }

        try {
            DB::transaction(function () use ($dPersona, $dAcademico, $dExperiencia, $dOtros, $request) {

                // ═══════════════════════════════
                // 1. GUARDAR PERSONA
                // ═══════════════════════════════
                $persona = PersonaModel::where('ci', $dPersona['ci'])->first() ?: new PersonaModel();

                // Traducción id_expedido → id_ci_expedido
                $expedidoRaw = $dPersona['id_expedido'] ?? $dPersona['id_ci_expedido'] ?? null;
                if ($expedidoRaw) {
                    if (!is_numeric($expedidoRaw)) {
                        $dPersona['id_ci_expedido'] = DB::table('departamentos')
                            ->where('codigo_expedido', $expedidoRaw)->value('id_departamento') ?? 1;
                    } else {
                        $dPersona['id_ci_expedido'] = (int) $expedidoRaw;
                    }
                }

                // Traducción nacionalidad texto → id_nacionalidad
                if (isset($dPersona['nacionalidad']) && !is_numeric($dPersona['nacionalidad'])) {
                    $dPersona['id_nacionalidad'] = DB::table('nacionalidades')
                        ->where('gentilicio', $dPersona['nacionalidad'])->value('id_nacionalidad') ?? 1;
                }

                // Traducción id_ciudad texto → FK int
                if (isset($dPersona['id_ciudad']) && !is_numeric($dPersona['id_ciudad'])) {
                    $dPersona['id_ciudad'] = DB::table('ciudades')
                        ->where('nombre', 'like', '%' . $dPersona['id_ciudad'] . '%')->value('id_ciudad') ?? 1;
                }

                // Extraer foto antes de limpiar
                $fotoB64 = $dPersona['foto'] ?? null;

                // ══ SOLO columnas reales de la tabla personas ══
                $personaClean = [
                    'primer_apellido'     => $dPersona['primer_apellido'] ?? null,
                    'segundo_apellido'    => $dPersona['segundo_apellido'] ?? null,
                    'nombres'             => $dPersona['nombres'] ?? null,
                    'ci'                  => $dPersona['ci'],
                    'complemento'         => $dPersona['complemento'] ?? null,
                    'id_ci_expedido'      => $dPersona['id_ci_expedido'] ?? 1,
                    'id_sexo'             => $dPersona['id_sexo'] ?? 1,
                    'fecha_nacimiento'    => $dPersona['fecha_nacimiento'] ?? null,
                    'celular_personal'    => $dPersona['celular_personal'] ?? '',
                    'correo_personal'     => $dPersona['correo_personal'] ?? '',
                    'estado_civil'        => $dPersona['estado_civil'] ?? '',
                    'id_nacionalidad'     => $dPersona['id_nacionalidad'] ?? 1,
                    'direccion_domicilio' => $dPersona['direccion_domicilio'] ?? '',
                    'id_ciudad'           => $dPersona['id_ciudad'] ?? 1,
                    'id_pais'             => $dPersona['id_pais'] ?? 2,
                ];

                $persona->fill($personaClean);
                $persona->estado_onboarding = 'completado';
                $persona->save();

                $id = $persona->id;

                // ═══════════════════════════════
                // HELPERS
                // ═══════════════════════════════

                // Guardar base64 como archivo
                $saveBase64 = function ($base64String, $tipo) use ($id, $persona) {
                    if (empty($base64String) || !str_starts_with($base64String, 'data:')) return null;

                    $parts = explode(',', $base64String);
                    if (count($parts) < 2) return null;

                    $mime = explode(';', $parts[0])[0];
                    $ext = explode('/', $mime)[1] ?? 'png';
                    $ext = str_replace('jpeg', 'jpg', $ext);
                    $fileData = base64_decode($parts[1]);

                    $fileName = $tipo . '_' . $id . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                    $filePath = 'documentos/' . $fileName;

                    Storage::disk('public')->put($filePath, $fileData);

                    if ($tipo === 'foto') {
                        $persona->foto = '/storage/' . $filePath;
                        $persona->save();
                        return '/storage/' . $filePath;
                    }

                    DocumentoPersonaModel::updateOrCreate(
                        ['id_persona' => $id, 'tipo' => ($tipo === 'ci_escaneado' ? 'ci' : $tipo)],
                        [
                            'nombre_archivo' => $fileName,
                            'ruta_archivo'   => '/storage/' . $filePath,
                            'formato'        => $ext
                        ]
                    );
                    return '/storage/' . $filePath;
                };

                // Procesar campo de archivo base64 embebido en items de méritos
                $processFile = function ($val, $subTipo) use ($id) {
                    if (!empty($val) && is_string($val) && str_starts_with($val, 'data:')) {
                        $parts = explode(',', $val);
                        if (count($parts) >= 2) {
                            $mime = explode(';', $parts[0])[0];
                            $ext = explode('/', $mime)[1] ?? 'pdf';
                            $ext = str_replace('jpeg', 'jpg', $ext);
                            $fileData = base64_decode($parts[1]);
                            $fileName = $subTipo . '_' . $id . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                            $filePath = 'documentos/' . $fileName;
                            Storage::disk('public')->put($filePath, $fileData);
                            return '/storage/' . $filePath;
                        }
                    }
                    if (is_string($val) && !empty($val) && !str_starts_with($val, 'data:')) {
                        return $val; // ya es una ruta guardada
                    }
                    return null;
                };

                // ═══════════════════════════════
                // DOCUMENTOS PERSONALES (Foto + CI)
                // ═══════════════════════════════
                if ($fotoB64) {
                    $saveBase64($fotoB64, 'foto');
                }
                $dArchivos = $request->input('archivos', []);
                if (!empty($dArchivos['ci_escaneado'])) {
                    $saveBase64($dArchivos['ci_escaneado'], 'ci_escaneado');
                }

                // ═══════════════════════════════
                // 2. ACADÉMICO
                // ═══════════════════════════════
                FormacionPregradoModel::where('id_persona', $id)->delete();
                FormacionPostgradoModel::where('id_persona', $id)->delete();

                foreach ($dAcademico as $a) {
                    if (($a['tipo_registro'] ?? '') === 'pregrado') {
                        $m = new FormacionPregradoModel();
                        $m->id_persona      = $id;
                        $m->nivel           = $a['nivel'] ?? '';
                        $m->institucion     = $a['institucion'] ?? '';
                        $m->carrera         = $a['titulo'] ?? ($a['carrera'] ?? '');
                        $m->fecha_diploma   = $a['fecha_emision_diploma'] ?? ($a['fecha_diploma'] ?? null);
                        $m->fecha_titulo    = $a['fecha_emision'] ?? ($a['fecha_titulo'] ?? null);
                        $m->id_depto        = $a['id_depto'] ?? null;
                        $m->archivo_diploma = $processFile($a['archivo_diploma'] ?? null, 'diploma');
                        $m->archivo_titulo  = $processFile($a['archivo_titulo'] ?? null, 'titulo_prov');
                        $m->save();
                    } else {
                        $m = new FormacionPostgradoModel();
                        $m->id_persona       = $id;
                        $m->tipo             = $a['tipo'] ?? '';
                        $m->nombre_programa  = $a['titulo'] ?? ($a['nombre_programa'] ?? '');
                        $m->institucion      = $a['institucion'] ?? '';
                        $m->fecha_diploma    = $a['fecha_emision'] ?? ($a['fecha_diploma'] ?? null);
                        $m->id_depto         = $a['id_depto'] ?? null;
                        $m->archivo_respaldo = $processFile($a['archivo_respaldo'] ?? null, 'respaldo_post');
                        $m->save();
                    }
                }

                // ═══════════════════════════════
                // 3. EXPERIENCIA
                // ═══════════════════════════════
                ExperienciaProfesionalModel::where('id_persona', $id)->delete();
                ExperienciaDocenteModel::where('id_persona', $id)->delete();

                foreach ($dExperiencia as $e) {
                    if (($e['tipo_registro'] ?? '') === 'profesional') {
                        $m = new ExperienciaProfesionalModel();
                        $m->id_persona       = $id;
                        $m->cargo            = $e['cargo'] ?? '';
                        $m->empresa          = $e['empresa'] ?? '';
                        $m->fecha_inicio     = $e['fecha_inicio'] ?? null;
                        $m->fecha_fin        = !empty($e['fecha_fin']) ? $e['fecha_fin'] : null;
                        $m->id_depto         = $e['id_depto'] ?? null;
                        $m->archivo_respaldo = $processFile($e['archivo_respaldo'] ?? null, 'respaldo_prof');
                        $m->save();
                    } else {
                        $m = new ExperienciaDocenteModel();
                        $m->id_persona       = $id;
                        $m->institucion      = $e['institucion'] ?? '';
                        $m->carrera          = $e['carrera'] ?? '';
                        $m->asignaturas      = $e['asignaturas'] ?? '';
                        $m->gestion_periodo  = $e['gestion_periodo'] ?? '';
                        $m->id_depto         = $e['id_depto'] ?? null;
                        $m->archivo_respaldo = $processFile($e['archivo_respaldo'] ?? null, 'respaldo_doc');
                        $m->save();
                    }
                }

                // ═══════════════════════════════
                // 4. OTROS MÉRITOS
                // ═══════════════════════════════
                CapacitacionModel::where('id_persona', $id)->delete();
                IdiomaModel::where('id_persona', $id)->delete();
                ReconocimientoModel::where('id_persona', $id)->delete();
                ProduccionIntelectualModel::where('id_persona', $id)->delete();

                foreach ($dOtros as $o) {
                    $tipo = $o['tipo_registro'] ?? '';

                    if ($tipo === 'capacitacion') {
                        $m = new CapacitacionModel();
                        $m->id_persona       = $id;
                        $m->nombre_curso     = $o['nombre_curso'] ?? '';
                        $m->institucion      = $o['institucion'] ?? '';
                        $m->fecha            = $o['fecha'] ?? null;
                        $m->carga_horaria    = (int)($o['carga_horaria'] ?? 0);
                        $m->id_depto         = $o['id_depto'] ?? null;
                        $m->archivo_respaldo = $processFile($o['archivo_respaldo'] ?? null, 'respaldo_cap');
                        $m->save();
                    } elseif ($tipo === 'idioma') {
                        $m = new IdiomaModel();
                        $m->id_persona       = $id;
                        $m->idioma           = $o['idioma'] ?? '';
                        $m->nivel_habla      = $o['nivel_habla'] ?? '';
                        $m->nivel_escritura  = $o['nivel_escritura'] ?? '';
                        $m->nivel_lee        = $o['nivel_lee'] ?? '';
                        $m->archivo_respaldo = $processFile($o['archivo_respaldo'] ?? null, 'respaldo_idioma');
                        $m->save();
                    } elseif ($tipo === 'produccion') {
                        $m = new ProduccionIntelectualModel();
                        $m->id_persona       = $id;
                        $m->tipo             = $o['tipo'] ?? '';
                        $m->titulo           = $o['titulo'] ?? '';
                        $m->fecha            = $o['fecha'] ?? null;
                        $m->editorial        = $o['editorial'] ?? '';
                        $m->id_depto         = $o['id_depto'] ?? null;
                        $m->archivo_respaldo = $processFile($o['archivo_respaldo'] ?? null, 'respaldo_prod');
                        $m->save();
                    } elseif ($tipo === 'reconocimiento') {
                        $m = new ReconocimientoModel();
                        $m->id_persona            = $id;
                        $m->titulo_premio         = $o['titulo_premio'] ?? '';
                        $m->institucion_otorgante = $o['institucion_otorgante'] ?? '';
                        $m->fecha                 = $o['fecha'] ?? null;
                        $m->lugar                 = $o['lugar'] ?? '';
                        $m->archivo_respaldo      = $processFile($o['archivo_respaldo'] ?? null, 'respaldo_rec');
                        $m->save();
                    }
                }

                // ═══════════════════════════════
                // 5. DESACTIVAR TOKEN
                // ═══════════════════════════════
                $token_str = $request->input('persona.session_key') ?? $request->input('token');
                if ($token_str && !Str::startsWith($token_str, 'new_session_')) {
                    OnboardingTokenModel::where('token', $token_str)->update(['usado_en' => now(), 'activo' => false]);
                }
            });

            return ApiResponse::success([], 'Registro completado con éxito');
        } catch (\Exception $e) {
            \Log::error('Error en completar registro: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return ApiResponse::error('Error al guardar: ' . $e->getMessage(), 500);
        }
    }
}
