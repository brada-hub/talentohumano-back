<?php

namespace Src\Academico\Infrastructure\Persistence;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Src\Academico\Domain\Repositories\AcademicoRepositoryInterface;
use Src\Personal\Infrastructure\Persistence\Models\PersonaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\CapacitacionModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ExperienciaDocenteModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ExperienciaProfesionalModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\FormacionPostgradoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\FormacionPregradoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\IdiomaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ProduccionIntelectualModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ReconocimientoModel;

final class EloquentAcademicoRepository implements AcademicoRepositoryInterface
{
    public function findProfileByPersonaId(string $personaId): ?array
    {
        $persona = PersonaModel::with([
            'formacionPregrado.depto.pais',
            'formacionPostgrado.depto.pais',
            'experienciaDocente.depto.pais',
            'experienciaProfesional.depto.pais',
            'capacitaciones.depto.pais',
            'produccionIntelectual.depto.pais',
            'reconocimientos',
            'idiomas',
        ])->find($personaId);

        if (!$persona) {
            return null;
        }

        return [
            'persona_id' => $persona->id,
            'persona' => [
                'nombres' => $persona->nombres,
                'primer_apellido' => $persona->primer_apellido,
                'segundo_apellido' => $persona->segundo_apellido,
                'ci' => $persona->ci,
            ],
            'formacion_pregrado' => $persona->formacionPregrado->toArray(),
            'formacion_postgrado' => $persona->formacionPostgrado->toArray(),
            'experiencia_docente' => $persona->experienciaDocente->toArray(),
            'experiencia_profesional' => $persona->experienciaProfesional->toArray(),
            'capacitaciones' => $persona->capacitaciones->toArray(),
            'produccion_intelectual' => $persona->produccionIntelectual->toArray(),
            'reconocimientos' => $persona->reconocimientos->toArray(),
            'idiomas' => $persona->idiomas->toArray(),
        ];
    }

    public function createPregrado(string $personaId, array $data): array
    {
        $record = new FormacionPregradoModel();
        $record->fill($this->extractPregradoAttributes($data));
        $record->id_persona = $personaId;
        $record->archivo_diploma = $this->storeUploadedFile($data['archivo_diploma'] ?? null, $personaId, 'pregrado_diploma');
        $record->archivo_titulo = $this->storeUploadedFile($data['archivo_titulo'] ?? null, $personaId, 'pregrado_titulo');
        $record->save();

        return $record->fresh()->toArray();
    }

    public function updatePregrado(int $id, array $data): ?array
    {
        $record = FormacionPregradoModel::find($id);
        if (!$record) {
            return null;
        }

        $record->fill($this->extractPregradoAttributes($data));
        $this->replaceUploadedFile($data['archivo_diploma'] ?? null, $record, 'archivo_diploma', 'pregrado_diploma');
        $this->replaceUploadedFile($data['archivo_titulo'] ?? null, $record, 'archivo_titulo', 'pregrado_titulo');
        $record->save();

        return $record->fresh()->toArray();
    }

    public function deletePregrado(int $id): bool
    {
        $record = FormacionPregradoModel::find($id);
        if (!$record) {
            return false;
        }

        $this->deleteStoredFile($record->archivo_diploma);
        $this->deleteStoredFile($record->archivo_titulo);
        $record->delete();

        return true;
    }

    public function createPostgrado(string $personaId, array $data): array
    {
        $record = new FormacionPostgradoModel();
        $record->fill($this->extractPostgradoAttributes($data));
        $record->id_persona = $personaId;
        $record->archivo_respaldo = $this->storeUploadedFile($data['archivo_respaldo'] ?? null, $personaId, 'postgrado_respaldo');
        $record->save();

        return $record->fresh()->toArray();
    }

    public function updatePostgrado(int $id, array $data): ?array
    {
        $record = FormacionPostgradoModel::find($id);
        if (!$record) {
            return null;
        }

        $record->fill($this->extractPostgradoAttributes($data));
        $this->replaceUploadedFile($data['archivo_respaldo'] ?? null, $record, 'archivo_respaldo', 'postgrado_respaldo');
        $record->save();

        return $record->fresh()->toArray();
    }

    public function deletePostgrado(int $id): bool
    {
        $record = FormacionPostgradoModel::find($id);
        if (!$record) {
            return false;
        }

        $this->deleteStoredFile($record->archivo_respaldo);
        $record->delete();

        return true;
    }

    public function createExperienciaDocente(string $personaId, array $data): array
    {
        $record = new ExperienciaDocenteModel();
        $record->fill($this->extractExperienciaDocenteAttributes($data));
        $record->id_persona = $personaId;
        $record->archivo_respaldo = $this->storeUploadedFile($data['archivo_respaldo'] ?? null, $personaId, 'experiencia_docente');
        $record->save();

        return $record->fresh()->toArray();
    }

    public function updateExperienciaDocente(int $id, array $data): ?array
    {
        $record = ExperienciaDocenteModel::find($id);
        if (!$record) {
            return null;
        }

        $record->fill($this->extractExperienciaDocenteAttributes($data));
        $this->replaceUploadedFile($data['archivo_respaldo'] ?? null, $record, 'archivo_respaldo', 'experiencia_docente');
        $record->save();

        return $record->fresh()->toArray();
    }

    public function deleteExperienciaDocente(int $id): bool
    {
        $record = ExperienciaDocenteModel::find($id);
        if (!$record) {
            return false;
        }

        $this->deleteStoredFile($record->archivo_respaldo);
        $record->delete();

        return true;
    }

    public function createExperienciaProfesional(string $personaId, array $data): array
    {
        $record = new ExperienciaProfesionalModel();
        $record->fill($this->extractExperienciaProfesionalAttributes($data));
        $record->id_persona = $personaId;
        $record->archivo_respaldo = $this->storeUploadedFile($data['archivo_respaldo'] ?? null, $personaId, 'experiencia_profesional');
        $record->save();

        return $record->fresh()->toArray();
    }

    public function updateExperienciaProfesional(int $id, array $data): ?array
    {
        $record = ExperienciaProfesionalModel::find($id);
        if (!$record) {
            return null;
        }

        $record->fill($this->extractExperienciaProfesionalAttributes($data));
        $this->replaceUploadedFile($data['archivo_respaldo'] ?? null, $record, 'archivo_respaldo', 'experiencia_profesional');
        $record->save();

        return $record->fresh()->toArray();
    }

    public function deleteExperienciaProfesional(int $id): bool
    {
        $record = ExperienciaProfesionalModel::find($id);
        if (!$record) {
            return false;
        }

        $this->deleteStoredFile($record->archivo_respaldo);
        $record->delete();

        return true;
    }

    public function createCapacitacion(string $personaId, array $data): array
    {
        $record = new CapacitacionModel();
        $record->fill($this->extractCapacitacionAttributes($data));
        $record->id_persona = $personaId;
        $record->archivo_respaldo = $this->storeUploadedFile($data['archivo_respaldo'] ?? null, $personaId, 'capacitacion');
        $record->save();
        return $record->fresh()->toArray();
    }

    public function updateCapacitacion(int $id, array $data): ?array
    {
        $record = CapacitacionModel::find($id);
        if (!$record) return null;
        $record->fill($this->extractCapacitacionAttributes($data));
        $this->replaceUploadedFile($data['archivo_respaldo'] ?? null, $record, 'archivo_respaldo', 'capacitacion');
        $record->save();
        return $record->fresh()->toArray();
    }

    public function deleteCapacitacion(int $id): bool
    {
        $record = CapacitacionModel::find($id);
        if (!$record) return false;
        $this->deleteStoredFile($record->archivo_respaldo);
        $record->delete();
        return true;
    }

    public function createIdioma(string $personaId, array $data): array
    {
        $record = new IdiomaModel();
        $record->fill($this->extractIdiomaAttributes($data));
        $record->id_persona = $personaId;
        $record->archivo_respaldo = $this->storeUploadedFile($data['archivo_respaldo'] ?? null, $personaId, 'idioma');
        $record->save();
        return $record->fresh()->toArray();
    }

    public function updateIdioma(int $id, array $data): ?array
    {
        $record = IdiomaModel::find($id);
        if (!$record) return null;
        $record->fill($this->extractIdiomaAttributes($data));
        $this->replaceUploadedFile($data['archivo_respaldo'] ?? null, $record, 'archivo_respaldo', 'idioma');
        $record->save();
        return $record->fresh()->toArray();
    }

    public function deleteIdioma(int $id): bool
    {
        $record = IdiomaModel::find($id);
        if (!$record) return false;
        $this->deleteStoredFile($record->archivo_respaldo);
        $record->delete();
        return true;
    }

    public function createProduccionIntelectual(string $personaId, array $data): array
    {
        $record = new ProduccionIntelectualModel();
        $record->fill($this->extractProduccionAttributes($data));
        $record->id_persona = $personaId;
        $record->archivo_respaldo = $this->storeUploadedFile($data['archivo_respaldo'] ?? null, $personaId, 'produccion');
        $record->save();
        return $record->fresh()->toArray();
    }

    public function updateProduccionIntelectual(int $id, array $data): ?array
    {
        $record = ProduccionIntelectualModel::find($id);
        if (!$record) return null;
        $record->fill($this->extractProduccionAttributes($data));
        $this->replaceUploadedFile($data['archivo_respaldo'] ?? null, $record, 'archivo_respaldo', 'produccion');
        $record->save();
        return $record->fresh()->toArray();
    }

    public function deleteProduccionIntelectual(int $id): bool
    {
        $record = ProduccionIntelectualModel::find($id);
        if (!$record) return false;
        $this->deleteStoredFile($record->archivo_respaldo);
        $record->delete();
        return true;
    }

    public function createReconocimiento(string $personaId, array $data): array
    {
        $record = new ReconocimientoModel();
        $record->fill($this->extractReconocimientoAttributes($data));
        $record->id_persona = $personaId;
        $record->archivo_respaldo = $this->storeUploadedFile($data['archivo_respaldo'] ?? null, $personaId, 'reconocimiento');
        $record->save();
        return $record->fresh()->toArray();
    }

    public function updateReconocimiento(int $id, array $data): ?array
    {
        $record = ReconocimientoModel::find($id);
        if (!$record) return null;
        $record->fill($this->extractReconocimientoAttributes($data));
        $this->replaceUploadedFile($data['archivo_respaldo'] ?? null, $record, 'archivo_respaldo', 'reconocimiento');
        $record->save();
        return $record->fresh()->toArray();
    }

    public function deleteReconocimiento(int $id): bool
    {
        $record = ReconocimientoModel::find($id);
        if (!$record) return false;
        $this->deleteStoredFile($record->archivo_respaldo);
        $record->delete();
        return true;
    }

    private function extractPregradoAttributes(array $data): array
    {
        return [
            'nivel' => $data['nivel'],
            'institucion' => $data['institucion'],
            'carrera' => $data['carrera'],
            'id_depto' => $data['id_depto'],
            'fecha_diploma' => $data['fecha_diploma'] ?? null,
            'fecha_titulo' => $data['fecha_titulo'] ?? null,
        ];
    }

    private function extractPostgradoAttributes(array $data): array
    {
        return [
            'tipo' => $data['tipo'],
            'nombre_programa' => $data['nombre_programa'],
            'institucion' => $data['institucion'],
            'id_depto' => $data['id_depto'],
            'fecha_diploma' => $data['fecha_diploma'] ?? null,
            'fecha_certificacion' => $data['fecha_certificacion'] ?? null,
        ];
    }

    private function extractExperienciaDocenteAttributes(array $data): array
    {
        return [
            'institucion' => $data['institucion'],
            'carrera' => $data['carrera'],
            'asignaturas' => $data['asignaturas'],
            'gestion_periodo' => $data['gestion_periodo'],
            'id_depto' => $data['id_depto'],
        ];
    }

    private function extractExperienciaProfesionalAttributes(array $data): array
    {
        return [
            'cargo' => $data['cargo'],
            'empresa' => $data['empresa'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'] ?? null,
            'id_depto' => $data['id_depto'],
        ];
    }

    private function extractCapacitacionAttributes(array $data): array
    {
        return [
            'nombre_curso' => $data['nombre_curso'],
            'institucion' => $data['institucion'],
            'fecha' => $data['fecha'] ?? null,
            'carga_horaria' => $data['carga_horaria'] ?? null,
            'id_depto' => $data['id_depto'],
        ];
    }

    private function extractIdiomaAttributes(array $data): array
    {
        return [
            'idioma' => $data['idioma'],
            'nivel_habla' => $data['nivel_habla'],
            'nivel_escritura' => $data['nivel_escritura'],
            'nivel_lee' => $data['nivel_lee'],
        ];
    }

    private function extractProduccionAttributes(array $data): array
    {
        return [
            'tipo' => $data['tipo'],
            'titulo' => $data['titulo'],
            'fecha' => $data['fecha'] ?? null,
            'editorial' => $data['editorial'] ?? null,
            'id_depto' => $data['id_depto'],
        ];
    }

    private function extractReconocimientoAttributes(array $data): array
    {
        return [
            'titulo_premio' => $data['titulo_premio'],
            'institucion_otorgante' => $data['institucion_otorgante'],
            'fecha' => $data['fecha'] ?? null,
            'lugar' => $data['lugar'] ?? null,
        ];
    }

    private function storeUploadedFile(?UploadedFile $file, string $personaId, string $prefix): ?string
    {
        if (!$file) {
            return null;
        }

        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'bin';
        $path = $file->storeAs(
            'documentos',
            sprintf('%s_%s_%s.%s', $prefix, $personaId, time(), $extension),
            'public'
        );

        return '/storage/' . $path;
    }

    private function replaceUploadedFile(?UploadedFile $file, object $record, string $recordField, string $prefix): void
    {
        if (!$file) {
            return;
        }

        $this->deleteStoredFile($record->{$recordField});
        $record->{$recordField} = $this->storeUploadedFile($file, (string) $record->id_persona, $prefix);
    }

    private function deleteStoredFile(?string $path): void
    {
        if (!$path) {
            return;
        }

        $relativePath = ltrim(str_replace('/storage/', '', $path), '/');
        Storage::disk('public')->delete($relativePath);
    }
}
