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
use DateTimeImmutable;

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
        // Simple mapping, not fully Domain Entity yet but useful for the logic extraction
        $persona = PersonaModel::where('ci', $ci)->first();
        if ($persona) {
            $fechaDb = $persona->fecha_nacimiento ? Carbon::parse($persona->fecha_nacimiento)->format('Y-m-d') : null;
            $fechaIn = Carbon::parse($birthDate)->format('Y-m-d');
            if ($fechaDb && $fechaDb !== $fechaIn) return null;
        }

        return $persona ? $persona->toArray() : null;
    }

    /**
     * ATENCIÓN: Este método centraliza la transacción masiva de guardado
     */
    public function saveFullOnboardingData(array $payload): void
    {
        $dPersona     = $payload['persona'] ?? [];
        $dAcademico   = $payload['academico'] ?? [];
        $dExperiencia = $payload['experiencia'] ?? [];
        $dOtros       = $payload['otros'] ?? [];

        DB::transaction(function () use ($dPersona, $dAcademico, $dExperiencia, $dOtros) {
            
            // 1. Guardar Persona (Traduciendo campos de texto a IDs)
            $idPersona = $this->upsertPersona($dPersona);

            // 2. Procesar Académico (Borrar y Recrear para simplicidad de sincronización)
            $this->syncAcademico($idPersona, $dAcademico);

            // 3. Procesar Experiencia
            $this->syncExperiencia($idPersona, $dExperiencia);

            // 4. Procesar Otros Méritos
            $this->syncOtrosMeritos($idPersona, $dOtros);
        });
    }

    private function upsertPersona(array $data): int
    {
        $persona = PersonaModel::where('ci', $data['ci'])->first() ?: new PersonaModel();
        
        // Traducciones de Ids (Blindando contra inputs no numéricos)
        $idExpedido = $this->resolveId('departamentos', 'codigo_expedido', $data['id_ci_expedido'] ?? ($data['id_expedido'] ?? 1), 1);
        $idNac      = $this->resolveId('nacionalidades', 'gentilicio', $data['id_nacionalidad'] ?? ($data['nacionalidad'] ?? 1), 1);
        $idCiudad   = $this->resolveId('ciudades', 'nombre', $data['id_ciudad'] ?? 1, 1);

        $persona->fill([
            'primer_apellido'     => $data['primer_apellido'] ?? null,
            'segundo_apellido'    => $data['segundo_apellido'] ?? null,
            'nombres'             => $data['nombres'] ?? null,
            'ci'                  => $data['ci'],
            'complemento'         => $data['complemento'] ?? null,
            'id_ci_expedido'      => $idExpedido,
            'id_sexo'             => $data['id_sexo'] ?? 1,
            'fecha_nacimiento'    => $data['fecha_nacimiento'] ?? null,
            'celular_personal'    => $data['celular_personal'] ?? '',
            'correo_personal'     => $data['correo_personal'] ?? '',
            'estado_civil'        => $data['estado_civil'] ?? '',
            'id_nacionalidad'     => $idNac,
            'direccion_domicilio' => $data['direccion_domicilio'] ?? '',
            'id_ciudad'           => $idCiudad,
            'id_pais'             => $data['id_pais'] ?? 2,
            'estado_onboarding'   => 'completado',
        ]);

        $persona->save();

        // Procesar Foto y CI Escaneado
        if (isset($data['foto'])) {
            $this->saveBase64ToFile($data['foto'], $persona->id, 'foto', true);
        }

        return $persona->id;
    }

    private function resolveId(string $table, string $column, $value, $default)
    {
        if (is_numeric($value)) return (int)$value;
        return DB::table($table)->where($column, 'like', '%'.$value.'%')->value('id_'.$table) ?? $default;
    }

    private function syncAcademico(int $id, array $data): void
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
                    'fecha_diploma'   => $a['fecha_diploma'] ?? null,
                    'fecha_titulo'    => $a['fecha_titulo'] ?? null,
                    'archivo_diploma' => $this->saveBase64ToFile($a['archivo_diploma'] ?? null, $id, 'diploma'),
                    'archivo_titulo'  => $this->saveBase64ToFile($a['archivo_titulo'] ?? null, $id, 'titulo_prov'),
                ]);
            } else {
                FormacionPostgradoModel::create([
                    'id_persona'       => $id,
                    'tipo'             => $a['tipo'] ?? '',
                    'nombre_programa'  => $a['titulo'] ?? ($a['nombre_programa'] ?? ''),
                    'institucion'      => $a['institucion'] ?? '',
                    'fecha_diploma'    => $a['fecha_diploma'] ?? null,
                    'archivo_respaldo' => $this->saveBase64ToFile($a['archivo_respaldo'] ?? null, $id, 'respaldo_post'),
                ]);
            }
        }
    }

    private function syncExperiencia(int $id, array $data): void
    {
        ExperienciaProfesionalModel::where('id_persona', $id)->delete();
        ExperienciaDocenteModel::where('id_persona', $id)->delete();

        foreach ($data as $e) {
            if (($e['tipo_registro'] ?? '') === 'profesional') {
                ExperienciaProfesionalModel::create([
                    'id_persona'       => $id,
                    'cargo'            => $e['cargo'] ?? '',
                    'empresa'          => $e['empresa'] ?? '',
                    'fecha_inicio'     => $e['fecha_inicio'] ?? null,
                    'fecha_fin'        => !empty($e['fecha_fin']) ? $e['fecha_fin'] : null,
                    'archivo_respaldo' => $this->saveBase64ToFile($e['archivo_respaldo'] ?? null, $id, 'respaldo_prof'),
                ]);
            } else {
                ExperienciaDocenteModel::create([
                    'id_persona'       => $id,
                    'institucion'      => $e['institucion'] ?? '',
                    'carrera'          => $e['carrera'] ?? '',
                    'asignaturas'      => $e['asignaturas'] ?? '',
                    'gestion_periodo'  => $e['gestion_periodo'] ?? '',
                    'archivo_respaldo' => $this->saveBase64ToFile($e['archivo_respaldo'] ?? null, $id, 'respaldo_doc'),
                ]);
            }
        }
    }

    private function syncOtrosMeritos(int $id, array $data): void
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
                    'fecha'     => $o['fecha'] ?? null,
                    'editorial' => $o['editorial'] ?? '',
                ]));
            } elseif ($tipo === 'reconocimiento') {
                ReconocimientoModel::create(array_merge($base, [
                    'titulo_premio'         => $o['titulo_premio'] ?? '',
                    'institucion_otorgante' => $o['institucion_otorgante'] ?? '',
                    'fecha'                 => $o['fecha'] ?? null,
                    'lugar'                 => $o['lugar'] ?? '',
                ]));
            }
        }
    }

    private function saveBase64ToFile($b64, int $idPersona, string $type, bool $isProfilePic = false): ?string
    {
        if (empty($b64) || !is_string($b64) || !str_starts_with($b64, 'data:')) return null;

        $parts = explode(',', $b64);
        if (count($parts) < 2) return null;

        $mime = explode(';', $parts[0])[0];
        $ext = explode('/', $mime)[1] ?? 'png';
        $ext = str_replace('jpeg', 'jpg', $ext);
        $fileData = base64_decode($parts[1]);

        $fileName = "{$type}_{$idPersona}_".time().".". $ext;
        $filePath = "documentos/{$fileName}";

        Storage::disk('public')->put($filePath, $fileData);
        $fullPath = "/storage/{$filePath}";

        if ($isProfilePic) {
            PersonaModel::where('id', $idPersona)->update(['foto' => $fullPath]);
        } else {
            DocumentoPersonaModel::updateOrCreate(
                ['id_persona' => $idPersona, 'tipo' => str_replace('respaldo_', '', $type)],
                ['nombre_archivo' => $fileName, 'ruta_archivo' => $fullPath, 'formato' => $ext]
            );
        }

        return $fullPath;
    }
}
