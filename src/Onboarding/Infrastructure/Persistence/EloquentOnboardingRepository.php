<?php

namespace Src\Onboarding\Infrastructure\Persistence;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Src\Onboarding\Domain\Entities\OnboardingToken;
use Src\Onboarding\Domain\Repositories\OnboardingRepositoryInterface;
use Src\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingTokenModel;
use Src\Personal\Infrastructure\Persistence\Models\PersonaModel;
use Src\Personal\Infrastructure\Persistence\Models\DocumentoPersonaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\FormacionPregradoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\FormacionPostgradoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ExperienciaDocenteModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ExperienciaProfesionalModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CapacitacionModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\IdiomaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ReconocimientoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ProduccionIntelectualModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EmpleadoModel;
use Src\Beneficios\Infrastructure\Persistence\Models\BeneficiarioModel;
use DateTimeImmutable;
use InvalidArgumentException;

final class EloquentOnboardingRepository implements OnboardingRepositoryInterface
{
    public function isPortalEnabled(): bool
    {
        $enabled = DB::table('_configs')
            ->where('key', 'onboarding_enabled')
            ->value('value');
        return !($enabled === '0' || $enabled === null);
    }

    public function findByToken(string $token): ?OnboardingToken
    {
        $model = OnboardingTokenModel::where('token', $token)->first();
        if (!$model) return null;

        return new OnboardingToken(
            $model->id,
            $model->token,
            $model->id_persona,
            (bool)$model->activo,
            $model->usado_en ? new DateTimeImmutable($model->usado_en) : null
        );
    }

    public function deactivateToken(string $token): void
    {
        OnboardingTokenModel::where('token', $token)
            ->update(['usado_en' => now(), 'activo' => false]);
    }

    public function saveToken(OnboardingToken $token): void
    {
        OnboardingTokenModel::updateOrCreate(
            ['id' => $token->id()],
            [
                'token'      => $token->token(),
                'id_persona' => $token->personaId(),
                'activo'     => $token->isActivo(),
                'usado_en'   => $token->usadoEn()?->format('Y-m-d H:i:s'),
            ]
        );
    }

    public function findPersonaByCiAndBirthDate(string $ci, string $birthDate): ?array
    {
        $fechaIn = Carbon::parse($birthDate)->format('Y-m-d');
        $ciNormalizado = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($ci)));

        $persona = PersonaModel::whereDate('fecha_nacimiento', $fechaIn)
            ->get()
            ->first(function (PersonaModel $persona) use ($ciNormalizado) {
                $ciPersona = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $persona->ci));
                $complemento = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) ($persona->complemento ?? '')));

                $candidatos = array_filter([
                    $ciPersona,
                    $complemento !== '' ? $ciPersona . $complemento : null,
                    preg_replace('/[A-Z]{2}$/', '', $ciPersona),
                ]);

                return in_array($ciNormalizado, $candidatos, true);
            });

        if (!$persona) return null;

        // CARGAR TODAS LAS RELACIONES PARA EL ONBOARDING
        $persona->load([
            'sexo', 'nacionalidad', 'ciudad', 'pais', 'expedido', 'documentos',
            'formacionPregrado', 'formacionPostgrado', 
            'experienciaDocente', 'experienciaProfesional',
            'capacitaciones', 'idiomas', 'produccionIntelectual', 'reconocimientos',
            'empleado.beneficiarios.parentesco', 'empleado.beneficiarios.expedido'
        ]);

        $data = $persona->toArray();
        $data['beneficiarios'] = $data['empleado']['beneficiarios'] ?? [];

        return $data;
    }

    /**
     * ATENCIÃƒâ€œN: Este mÃƒÂ©todo centraliza la transacciÃƒÂ³n masiva de guardado
     */
    public function saveFullOnboardingData(array $payload, ?string $forcedPersonaId = null): void
    {
        $dPersona     = $payload['persona'] ?? [];
        $dBeneficiarios = $payload['beneficiarios'] ?? [];
        $dAcademico   = $payload['academico'] ?? [];
        $dExperiencia = $payload['experiencia'] ?? [];
        $dOtros       = $payload['otros'] ?? [];
        $dArchivos    = $payload['archivos'] ?? [];

        DB::transaction(function () use ($dPersona, $dBeneficiarios, $dAcademico, $dExperiencia, $dOtros, $dArchivos, $forcedPersonaId) {
            
            // 1. Guardar Persona y sus Documentos Base (Foto, CI)
            $idPersona = $this->upsertPersona($dPersona, $dArchivos, $forcedPersonaId);

            // 1.1 Guardar Datos de Empleado (Seguro Social, etc)
            $this->upsertEmpleado($idPersona, $dPersona);

            // 1.2 Guardar Beneficiarios
            $this->syncBeneficiarios($idPersona, $dBeneficiarios);

            // 2. Procesar AcadÃƒÂ©mico
            $this->syncAcademico($idPersona, $dAcademico);

            // 3. Procesar Experiencia
            $this->syncExperiencia($idPersona, $dExperiencia);

            // 4. Procesar Otros MÃƒÂ©ritos
            $this->syncOtrosMeritos($idPersona, $dOtros);
        });
    }

    private function upsertPersona(array $data, array $archivos = [], ?string $forcedPersonaId = null): string
    {
        $persona = $this->findExistingPersonaForUpsert($data, $forcedPersonaId) ?: new PersonaModel();
        
        // Traducciones de Ids (Blindando contra inputs no numÃ©ricos)
        $idExpedido = $this->resolveId('departamentos', 'codigo_expedido', $data['id_ci_expedido'] ?? ($data['id_expedido'] ?? null), $persona->id_ci_expedido ?? 1);
        $idNac      = $this->resolveId('nacionalidades', 'gentilicio', $data['id_nacionalidad'] ?? ($data['nacionalidad'] ?? null), $persona->id_nacionalidad ?? 1);
        $idCiudad   = $this->resolveId('ciudades', 'nombre', $data['id_ciudad'] ?? null, $persona->id_ciudad ?? null);

        $persona->fill([
            'primer_apellido'     => $data['primer_apellido'] ?? null,
            'segundo_apellido'    => $data['segundo_apellido'] ?? null,
            'nombres'             => $data['nombres'] ?? null,
            'ci'                  => $data['ci'],
            'complemento'         => $data['complemento'] ?? null,
            'tratamiento'         => $data['tratamiento'] ?? null,
            'id_ci_expedido'      => $idExpedido,
            'id_sexo'             => $data['id_sexo'] ?? 1,
            'fecha_nacimiento'    => $data['fecha_nacimiento'] ?? null,
            'celular_personal'    => $data['celular_personal'] ?? '',
            'correo_personal'     => $data['correo_personal'] ?? '',
            'estado_civil'        => $data['estado_civil'] ?? '',
            'id_nacionalidad'     => $idNac,
            'direccion_domicilio' => $data['direccion_domicilio'] ?? '',
            'id_depto_residencia' => $data['id_depto_residencia'] ?? null,
            'id_ciudad'           => $idCiudad,
            'id_pais'             => $data['id_pais'] ?? 2,
            'estado_onboarding'   => 'completado',
        ]);

        $persona->save();

        // Procesar Foto y CI Escaneado (Desde el objeto persona O desde el objeto archivos)
        $fotoB64 = $data['foto'] ?? ($archivos['foto'] ?? null);
        if ($fotoB64) {
            $this->saveBase64ToFile($fotoB64, $persona->id, 'foto', true);
        }

        $ciB64 = $archivos['ci_escaneado'] ?? ($data['ci_escaneado'] ?? null);
        if ($ciB64) {
            $this->saveBase64ToFile($ciB64, $persona->id, 'ci_escaneado', false, true);
        }

        return (string)$persona->id;
    }

    private function findExistingPersonaForUpsert(array $data, ?string $forcedPersonaId = null): ?PersonaModel
    {
        if ($forcedPersonaId) {
            $persona = PersonaModel::find($forcedPersonaId);
            if ($persona) {
                return $persona;
            }
        }

        if (!empty($data['id'])) {
            $persona = PersonaModel::find((string) $data['id']);
            if ($persona) {
                return $persona;
            }
        }

        if (empty($data['ci'])) {
            return null;
        }

        $ciNormalizado = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim((string) $data['ci'])));

        return PersonaModel::all()->first(function (PersonaModel $persona) use ($ciNormalizado) {
            $ciPersona = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $persona->ci));
            $complemento = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) ($persona->complemento ?? '')));

            $candidatos = array_filter([
                $ciPersona,
                $complemento !== '' ? $ciPersona . $complemento : null,
                preg_replace('/[A-Z]{2}$/', '', $ciPersona),
            ]);

            return in_array($ciNormalizado, $candidatos, true);
        });
    }

    private function upsertEmpleado(string $idPersona, array $data): void
    {
        // El onboarding suele recolectar datos de seguridad social y correos
        $empleado = EmpleadoModel::where('id_persona', $idPersona)->first() ?: new EmpleadoModel();

        $idCaja    = $this->resolveId('caja_salud', 'nombre', $data['id_caja'] ?? ($data['caja'] ?? null), null);
        $idPension = $this->resolveId('entidad_pensiones', 'nombre', $data['id_entidad_pensiones'] ?? ($data['pensiones'] ?? null), null);

        $empleado->fill([
            'id_persona'            => $idPersona,
            'celular_institucional' => $data['celular_institucional'] ?? ($data['celular_trabajo'] ?? null),
            'correo_institucional'  => $data['correo_institucional'] ?? ($data['correo_trabajo'] ?? null),
            'id_caja'               => $idCaja,
            'nro_matricula_seguro'  => $data['nro_matricula_seguro'] ?? ($data['matricula_seguro'] ?? null),
            'id_entidad_pensiones'  => $idPension,
            'nro_nua_cua'           => $data['nro_nua_cua'] ?? ($data['nua_cua'] ?? null),
            'estado_laboral'        => 'Activo', // Al completar el onboarding, lo marcamos como activo
        ]);

        $empleado->save();
    }

    private function resolveId(string $table, string $column, $value, $default)
    {
        // Si ya es numÃƒÂ©rico, lo devolvemos tal cual
        if (is_numeric($value)) return (int)$value;
        
        // Si viene como objeto/array desde el frontend (comÃƒÂºn en Quasar q-select)
        if (is_array($value) || is_object($value)) {
            $value = (array)$value;
            // Intentar encontrar una llave que sea 'id_TABLA' o 'id'
            return $value['id_'.$table] ?? ($value['id'] ?? $default);
        }

        if (empty($value)) return $default;

        return DB::table($table)->where($column, 'like', '%'.$value.'%')->value('id_'.$table) ?? $default;
    }

    private function syncAcademico(string $id, array $data): void
    {
        FormacionPregradoModel::where('id_persona', $id)->delete();
        FormacionPostgradoModel::where('id_persona', $id)->delete();

        foreach ($data as $a) {
            if (($a['tipo_registro'] ?? '') === 'pregrado') {
                FormacionPregradoModel::create([
                    'id_persona'      => $id,
                    'nivel'           => $a['nivel'] ?? '',
                    'institucion'     => $a['institucion'] ?? '',
                    'carrera'         => $a['titulo'] ?? ($a['carrera'] ?? ''),
                    'id_depto'        => $a['id_depto'] ?? 1,
                    'fecha_diploma'   => $a['fecha_diploma'] ?? ($a['fecha_emision_diploma'] ?? null),
                    'fecha_titulo'    => $a['fecha_titulo'] ?? ($a['fecha_emision'] ?? null),
                    'archivo_diploma' => $this->saveBase64ToFile($a['archivo_diploma'] ?? null, $id, 'diploma'),
                    'archivo_titulo'  => $this->saveBase64ToFile($a['archivo_titulo'] ?? null, $id, 'titulo_prov'),
                ]);
            } else {
                FormacionPostgradoModel::create([
                    'id_persona'       => $id,
                    'tipo'             => $a['tipo'] ?? '',
                    'nombre_programa'  => $a['titulo'] ?? ($a['nombre_programa'] ?? ''),
                    'institucion'      => $a['institucion'] ?? '',
                    'id_depto'         => $a['id_depto'] ?? 1,
                    'fecha_diploma'    => $a['fecha_diploma'] ?? null,
                    'fecha_certificacion' => $a['fecha_certificacion'] ?? ($a['fecha_emision'] ?? null),
                    'archivo_respaldo' => $this->saveBase64ToFile($a['archivo_respaldo'] ?? null, $id, 'respaldo_post'),
                ]);
            }
        }
    }

    private function syncExperiencia(string $id, array $data): void
    {
        ExperienciaProfesionalModel::where('id_persona', $id)->delete();
        ExperienciaDocenteModel::where('id_persona', $id)->delete();

        foreach ($data as $e) {
            if (($e['tipo_registro'] ?? '') === 'profesional') {
                ExperienciaProfesionalModel::create([
                    'id_persona'       => $id,
                    'cargo'            => $e['cargo'] ?? '',
                    'empresa'          => $e['empresa'] ?? '',
                    'id_depto'         => $e['id_depto'] ?? 1,
                    'fecha_inicio'     => $e['fecha_inicio'] ?? null,
                    'fecha_fin'        => !empty($e['fecha_fin']) ? $e['fecha_fin'] : null,
                    'archivo_respaldo' => $this->saveBase64ToFile($e['archivo_respaldo'] ?? null, $id, 'respaldo_exp_prof'),
                ]);
            } else {
                ExperienciaDocenteModel::create([
                    'id_persona'       => $id,
                    'institucion'      => $e['institucion'] ?? '',
                    'carrera'          => $e['carrera'] ?? '',
                    'asignaturas'      => $e['asignaturas'] ?? '',
                    'id_depto'         => $e['id_depto'] ?? 1,
                    'gestion_periodo'  => $e['gestion_periodo'] ?? '',
                    'archivo_respaldo' => $this->saveBase64ToFile($e['archivo_respaldo'] ?? null, $id, 'respaldo_exp_doc'),
                ]);
            }
        }
    }

    private function syncOtrosMeritos(string $id, array $data): void
    {
        CapacitacionModel::where('id_persona', $id)->delete();
        IdiomaModel::where('id_persona', $id)->delete();
        ReconocimientoModel::where('id_persona', $id)->delete();
        ProduccionIntelectualModel::where('id_persona', $id)->delete();

        foreach ($data as $o) {
            $tipo = $o['tipo_registro'] ?? '';
            $base = ['id_persona' => $id, 'archivo_respaldo' => $this->saveBase64ToFile($o['archivo_respaldo'] ?? null, $id, 'respaldo_'.$tipo)];
            
            if ($tipo === 'capacitacion') {
                CapacitacionModel::create(array_merge($base, [
                    'nombre_curso' => $o['nombre_curso'] ?? '',
                    'institucion'  => $o['institucion'] ?? '',
                    'id_depto'     => $o['id_depto'] ?? 1,
                    'fecha'         => $o['fecha'] ?? null,
                    'carga_horaria' => (int)($o['carga_horaria'] ?? 0),
                ]));
            } elseif ($tipo === 'idioma') {
                IdiomaModel::create(array_merge($base, [
                    'idioma'          => $o['idioma'] ?? '',
                    'nivel_habla'     => $o['nivel_habla'] ?? '',
                    'nivel_escritura' => $o['nivel_escritura'] ?? '',
                    'nivel_lee'       => $o['nivel_lee'] ?? '',
                ]));
            } elseif ($tipo === 'produccion') {
                ProduccionIntelectualModel::create(array_merge($base, [
                    'tipo'      => $o['tipo'] ?? '',
                    'titulo'    => $o['titulo'] ?? '',
                    'id_depto'  => $o['id_depto'] ?? 1,
                    'fecha'     => $o['fecha'] ?? null,
                    'editorial' => $o['editorial'] ?? '',
                ]));
            } elseif ($tipo === 'reconocimiento') {
                ReconocimientoModel::create(array_merge($base, [
                    'titulo_premio'         => $o['titulo_premio'] ?? '',
                    'institucion_otorgante' => $o['institucion_otorgante'] ?? '',
                    'id_depto'              => $o['id_depto'] ?? 1,
                    'fecha'                 => $o['fecha'] ?? null,
                    'lugar'                 => $o['lugar'] ?? '',
                ]));
            }
        }
    }

    private function syncBeneficiarios(string $idPersona, array $data): void
    {
        $empleado = EmpleadoModel::query()->where('id_persona', $idPersona)->first();
        if (!$empleado) {
            return;
        }

        BeneficiarioModel::query()->where('id_empleado', $empleado->id_empleado)->delete();

        foreach (array_slice($data, 0, 2) as $beneficiario) {
            BeneficiarioModel::query()->create([
                'id_empleado' => $empleado->id_empleado,
                'primer_apellido' => $beneficiario['primer_apellido'] ?? '',
                'segundo_apellido' => $beneficiario['segundo_apellido'] ?? null,
                'nombres' => $beneficiario['nombres'] ?? '',
                'ci' => $beneficiario['ci'] ?? null,
                'complemento' => $beneficiario['complemento'] ?? null,
                'id_ci_expedido' => $beneficiario['id_ci_expedido'] ?? null,
                'fecha_nacimiento' => $beneficiario['fecha_nacimiento'] ?? null,
                'id_parentesco' => $beneficiario['id_parentesco'] ?? null,
            ]);
        }
    }

    private function saveBase64ToFile($b64, string $idPersona, string $type, bool $isProfilePic = false, bool $isPersonaDoc = false): ?string
    {
        if (empty($b64) || !is_string($b64) || !str_starts_with($b64, 'data:')) return null;

        $parts = explode(',', $b64);
        if (count($parts) < 2) return null;

        $mime = explode(';', $parts[0])[0];
        $ext = explode('/', $mime)[1] ?? 'png';
        $ext = str_replace(['jpeg', 'pdf'], ['jpg', 'pdf'], $ext); // Normalizar
        if (str_contains($mime, 'pdf')) $ext = 'pdf'; 

        $fileData = base64_decode($parts[1], true);
        if ($fileData === false) {
            throw new InvalidArgumentException('No se pudo procesar uno de los archivos adjuntos.');
        }

        if (strlen($fileData) > 1024 * 1024) {
            throw new InvalidArgumentException('Los archivos adjuntos no deben superar 1 MB.');
        }

        $fileName = "{$type}_{$idPersona}_".time().".". $ext;
        $filePath = "documentos/{$fileName}";

        Storage::disk('public')->put($filePath, $fileData);
        $fullPath = "/storage/{$filePath}";

        if ($isProfilePic) {
            PersonaModel::where('id', $idPersona)->update(['foto' => $fullPath]);
        } elseif ($isPersonaDoc) {
            DocumentoPersonaModel::updateOrCreate(
                ['id_persona' => $idPersona, 'tipo' => str_replace(['respaldo_', '_escaneado'], ['', ''], $type)],
                ['nombre_archivo' => $fileName, 'ruta_archivo' => $fullPath, 'formato' => $ext]
            );
        }

        return $fullPath;
    }
}



