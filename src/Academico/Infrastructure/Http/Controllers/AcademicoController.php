<?php

namespace Src\Academico\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Src\Academico\Application\Experiencia\CreateExperienciaDocenteHandler;
use Src\Academico\Application\Experiencia\CreateExperienciaProfesionalHandler;
use Src\Academico\Application\Experiencia\DeleteExperienciaDocenteHandler;
use Src\Academico\Application\Experiencia\DeleteExperienciaProfesionalHandler;
use Src\Academico\Application\Experiencia\UpdateExperienciaDocenteHandler;
use Src\Academico\Application\Experiencia\UpdateExperienciaProfesionalHandler;
use Src\Academico\Application\Formacion\CreatePostgradoHandler;
use Src\Academico\Application\Formacion\CreatePregradoHandler;
use Src\Academico\Application\Formacion\DeletePostgradoHandler;
use Src\Academico\Application\Formacion\DeletePregradoHandler;
use Src\Academico\Application\Formacion\UpdatePostgradoHandler;
use Src\Academico\Application\Formacion\UpdatePregradoHandler;
use Src\Academico\Application\GetAcademicProfileHandler;
use Src\Academico\Application\Otros\CreateCapacitacionHandler;
use Src\Academico\Application\Otros\CreateIdiomaHandler;
use Src\Academico\Application\Otros\CreateProduccionIntelectualHandler;
use Src\Academico\Application\Otros\CreateReconocimientoHandler;
use Src\Academico\Application\Otros\DeleteCapacitacionHandler;
use Src\Academico\Application\Otros\DeleteIdiomaHandler;
use Src\Academico\Application\Otros\DeleteProduccionIntelectualHandler;
use Src\Academico\Application\Otros\DeleteReconocimientoHandler;
use Src\Academico\Application\Otros\UpdateCapacitacionHandler;
use Src\Academico\Application\Otros\UpdateIdiomaHandler;
use Src\Academico\Application\Otros\UpdateProduccionIntelectualHandler;
use Src\Academico\Application\Otros\UpdateReconocimientoHandler;
use Src\Academico\Infrastructure\Http\Requests\CreateCapacitacionRequest;
use Src\Academico\Infrastructure\Http\Requests\CreatePostgradoRequest;
use Src\Academico\Infrastructure\Http\Requests\CreatePregradoRequest;
use Src\Academico\Infrastructure\Http\Requests\CreateExperienciaDocenteRequest;
use Src\Academico\Infrastructure\Http\Requests\CreateExperienciaProfesionalRequest;
use Src\Academico\Infrastructure\Http\Requests\CreateIdiomaRequest;
use Src\Academico\Infrastructure\Http\Requests\CreateProduccionIntelectualRequest;
use Src\Academico\Infrastructure\Http\Requests\CreateReconocimientoRequest;
use Src\Academico\Infrastructure\Http\Requests\UpdateCapacitacionRequest;
use Src\Academico\Infrastructure\Http\Requests\UpdatePostgradoRequest;
use Src\Academico\Infrastructure\Http\Requests\UpdatePregradoRequest;
use Src\Academico\Infrastructure\Http\Requests\UpdateExperienciaDocenteRequest;
use Src\Academico\Infrastructure\Http\Requests\UpdateExperienciaProfesionalRequest;
use Src\Academico\Infrastructure\Http\Requests\UpdateIdiomaRequest;
use Src\Academico\Infrastructure\Http\Requests\UpdateProduccionIntelectualRequest;
use Src\Academico\Infrastructure\Http\Requests\UpdateReconocimientoRequest;
use Src\Shared\Infrastructure\Http\ApiResponse;

final class AcademicoController
{
    public function __construct(
        private readonly GetAcademicProfileHandler $getAcademicProfileHandler,
        private readonly CreatePregradoHandler $createPregradoHandler,
        private readonly UpdatePregradoHandler $updatePregradoHandler,
        private readonly DeletePregradoHandler $deletePregradoHandler,
        private readonly CreatePostgradoHandler $createPostgradoHandler,
        private readonly UpdatePostgradoHandler $updatePostgradoHandler,
        private readonly DeletePostgradoHandler $deletePostgradoHandler,
        private readonly CreateExperienciaDocenteHandler $createExperienciaDocenteHandler,
        private readonly UpdateExperienciaDocenteHandler $updateExperienciaDocenteHandler,
        private readonly DeleteExperienciaDocenteHandler $deleteExperienciaDocenteHandler,
        private readonly CreateExperienciaProfesionalHandler $createExperienciaProfesionalHandler,
        private readonly UpdateExperienciaProfesionalHandler $updateExperienciaProfesionalHandler,
        private readonly DeleteExperienciaProfesionalHandler $deleteExperienciaProfesionalHandler,
        private readonly CreateCapacitacionHandler $createCapacitacionHandler,
        private readonly UpdateCapacitacionHandler $updateCapacitacionHandler,
        private readonly DeleteCapacitacionHandler $deleteCapacitacionHandler,
        private readonly CreateIdiomaHandler $createIdiomaHandler,
        private readonly UpdateIdiomaHandler $updateIdiomaHandler,
        private readonly DeleteIdiomaHandler $deleteIdiomaHandler,
        private readonly CreateProduccionIntelectualHandler $createProduccionIntelectualHandler,
        private readonly UpdateProduccionIntelectualHandler $updateProduccionIntelectualHandler,
        private readonly DeleteProduccionIntelectualHandler $deleteProduccionIntelectualHandler,
        private readonly CreateReconocimientoHandler $createReconocimientoHandler,
        private readonly UpdateReconocimientoHandler $updateReconocimientoHandler,
        private readonly DeleteReconocimientoHandler $deleteReconocimientoHandler,
    ) {}

    public function showByPersona(string $personaId): JsonResponse
    {
        $profile = $this->getAcademicProfileHandler->handle($personaId);
        if (!$profile) {
            return ApiResponse::notFound('Persona no encontrada');
        }

        return ApiResponse::success($profile, 'Perfil academico cargado');
    }

    public function storePregrado(CreatePregradoRequest $request, string $personaId): JsonResponse
    {
        $record = $this->createPregradoHandler->handle($personaId, $request->validated() + $request->allFiles());
        return ApiResponse::created($record, 'Formacion de pregrado registrada');
    }

    public function updatePregrado(UpdatePregradoRequest $request, int $id): JsonResponse
    {
        $record = $this->updatePregradoHandler->handle($id, $request->validated() + $request->allFiles());
        if (!$record) {
            return ApiResponse::notFound('Registro de pregrado no encontrado');
        }

        return ApiResponse::success($record, 'Formacion de pregrado actualizada');
    }

    public function destroyPregrado(int $id): JsonResponse
    {
        if (!$this->deletePregradoHandler->handle($id)) {
            return ApiResponse::notFound('Registro de pregrado no encontrado');
        }

        return ApiResponse::success([], 'Formacion de pregrado eliminada');
    }

    public function storePostgrado(CreatePostgradoRequest $request, string $personaId): JsonResponse
    {
        $record = $this->createPostgradoHandler->handle($personaId, $request->validated() + $request->allFiles());
        return ApiResponse::created($record, 'Formacion de postgrado registrada');
    }

    public function updatePostgrado(UpdatePostgradoRequest $request, int $id): JsonResponse
    {
        $record = $this->updatePostgradoHandler->handle($id, $request->validated() + $request->allFiles());
        if (!$record) {
            return ApiResponse::notFound('Registro de postgrado no encontrado');
        }

        return ApiResponse::success($record, 'Formacion de postgrado actualizada');
    }

    public function destroyPostgrado(int $id): JsonResponse
    {
        if (!$this->deletePostgradoHandler->handle($id)) {
            return ApiResponse::notFound('Registro de postgrado no encontrado');
        }

        return ApiResponse::success([], 'Formacion de postgrado eliminada');
    }

    public function storeExperienciaDocente(CreateExperienciaDocenteRequest $request, string $personaId): JsonResponse
    {
        $record = $this->createExperienciaDocenteHandler->handle($personaId, $request->validated() + $request->allFiles());
        return ApiResponse::created($record, 'Experiencia docente registrada');
    }

    public function updateExperienciaDocente(UpdateExperienciaDocenteRequest $request, int $id): JsonResponse
    {
        $record = $this->updateExperienciaDocenteHandler->handle($id, $request->validated() + $request->allFiles());
        if (!$record) {
            return ApiResponse::notFound('Registro de experiencia docente no encontrado');
        }

        return ApiResponse::success($record, 'Experiencia docente actualizada');
    }

    public function destroyExperienciaDocente(int $id): JsonResponse
    {
        if (!$this->deleteExperienciaDocenteHandler->handle($id)) {
            return ApiResponse::notFound('Registro de experiencia docente no encontrado');
        }

        return ApiResponse::success([], 'Experiencia docente eliminada');
    }

    public function storeExperienciaProfesional(CreateExperienciaProfesionalRequest $request, string $personaId): JsonResponse
    {
        $record = $this->createExperienciaProfesionalHandler->handle($personaId, $request->validated() + $request->allFiles());
        return ApiResponse::created($record, 'Experiencia profesional registrada');
    }

    public function updateExperienciaProfesional(UpdateExperienciaProfesionalRequest $request, int $id): JsonResponse
    {
        $record = $this->updateExperienciaProfesionalHandler->handle($id, $request->validated() + $request->allFiles());
        if (!$record) {
            return ApiResponse::notFound('Registro de experiencia profesional no encontrado');
        }

        return ApiResponse::success($record, 'Experiencia profesional actualizada');
    }

    public function destroyExperienciaProfesional(int $id): JsonResponse
    {
        if (!$this->deleteExperienciaProfesionalHandler->handle($id)) {
            return ApiResponse::notFound('Registro de experiencia profesional no encontrado');
        }

        return ApiResponse::success([], 'Experiencia profesional eliminada');
    }

    public function storeCapacitacion(CreateCapacitacionRequest $request, string $personaId): JsonResponse
    {
        $record = $this->createCapacitacionHandler->handle($personaId, $request->validated() + $request->allFiles());
        return ApiResponse::created($record, 'Capacitacion registrada');
    }

    public function updateCapacitacion(UpdateCapacitacionRequest $request, int $id): JsonResponse
    {
        $record = $this->updateCapacitacionHandler->handle($id, $request->validated() + $request->allFiles());
        if (!$record) return ApiResponse::notFound('Registro de capacitacion no encontrado');
        return ApiResponse::success($record, 'Capacitacion actualizada');
    }

    public function destroyCapacitacion(int $id): JsonResponse
    {
        if (!$this->deleteCapacitacionHandler->handle($id)) {
            return ApiResponse::notFound('Registro de capacitacion no encontrado');
        }
        return ApiResponse::success([], 'Capacitacion eliminada');
    }

    public function storeIdioma(CreateIdiomaRequest $request, string $personaId): JsonResponse
    {
        $record = $this->createIdiomaHandler->handle($personaId, $request->validated() + $request->allFiles());
        return ApiResponse::created($record, 'Idioma registrado');
    }

    public function updateIdioma(UpdateIdiomaRequest $request, int $id): JsonResponse
    {
        $record = $this->updateIdiomaHandler->handle($id, $request->validated() + $request->allFiles());
        if (!$record) return ApiResponse::notFound('Registro de idioma no encontrado');
        return ApiResponse::success($record, 'Idioma actualizado');
    }

    public function destroyIdioma(int $id): JsonResponse
    {
        if (!$this->deleteIdiomaHandler->handle($id)) {
            return ApiResponse::notFound('Registro de idioma no encontrado');
        }
        return ApiResponse::success([], 'Idioma eliminado');
    }

    public function storeProduccionIntelectual(CreateProduccionIntelectualRequest $request, string $personaId): JsonResponse
    {
        $record = $this->createProduccionIntelectualHandler->handle($personaId, $request->validated() + $request->allFiles());
        return ApiResponse::created($record, 'Produccion intelectual registrada');
    }

    public function updateProduccionIntelectual(UpdateProduccionIntelectualRequest $request, int $id): JsonResponse
    {
        $record = $this->updateProduccionIntelectualHandler->handle($id, $request->validated() + $request->allFiles());
        if (!$record) return ApiResponse::notFound('Registro de produccion intelectual no encontrado');
        return ApiResponse::success($record, 'Produccion intelectual actualizada');
    }

    public function destroyProduccionIntelectual(int $id): JsonResponse
    {
        if (!$this->deleteProduccionIntelectualHandler->handle($id)) {
            return ApiResponse::notFound('Registro de produccion intelectual no encontrado');
        }
        return ApiResponse::success([], 'Produccion intelectual eliminada');
    }

    public function storeReconocimiento(CreateReconocimientoRequest $request, string $personaId): JsonResponse
    {
        $record = $this->createReconocimientoHandler->handle($personaId, $request->validated() + $request->allFiles());
        return ApiResponse::created($record, 'Reconocimiento registrado');
    }

    public function updateReconocimiento(UpdateReconocimientoRequest $request, int $id): JsonResponse
    {
        $record = $this->updateReconocimientoHandler->handle($id, $request->validated() + $request->allFiles());
        if (!$record) return ApiResponse::notFound('Registro de reconocimiento no encontrado');
        return ApiResponse::success($record, 'Reconocimiento actualizado');
    }

    public function destroyReconocimiento(int $id): JsonResponse
    {
        if (!$this->deleteReconocimientoHandler->handle($id)) {
            return ApiResponse::notFound('Registro de reconocimiento no encontrado');
        }
        return ApiResponse::success([], 'Reconocimiento eliminado');
    }
}
