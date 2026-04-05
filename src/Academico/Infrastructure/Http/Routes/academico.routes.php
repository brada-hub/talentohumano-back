<?php

use Illuminate\Support\Facades\Route;
use Src\Academico\Infrastructure\Http\Controllers\AcademicoController;

Route::prefix('v1/academico')->middleware('auth:sanctum')->group(function () {
    Route::get('/personas/{personaId}', [AcademicoController::class, 'showByPersona']);
    Route::post('/personas/{personaId}/formacion-pregrado', [AcademicoController::class, 'storePregrado']);
    Route::put('/formacion-pregrado/{id}', [AcademicoController::class, 'updatePregrado']);
    Route::delete('/formacion-pregrado/{id}', [AcademicoController::class, 'destroyPregrado']);

    Route::post('/personas/{personaId}/formacion-postgrado', [AcademicoController::class, 'storePostgrado']);
    Route::put('/formacion-postgrado/{id}', [AcademicoController::class, 'updatePostgrado']);
    Route::delete('/formacion-postgrado/{id}', [AcademicoController::class, 'destroyPostgrado']);

    Route::post('/personas/{personaId}/experiencia-docente', [AcademicoController::class, 'storeExperienciaDocente']);
    Route::put('/experiencia-docente/{id}', [AcademicoController::class, 'updateExperienciaDocente']);
    Route::delete('/experiencia-docente/{id}', [AcademicoController::class, 'destroyExperienciaDocente']);

    Route::post('/personas/{personaId}/experiencia-profesional', [AcademicoController::class, 'storeExperienciaProfesional']);
    Route::put('/experiencia-profesional/{id}', [AcademicoController::class, 'updateExperienciaProfesional']);
    Route::delete('/experiencia-profesional/{id}', [AcademicoController::class, 'destroyExperienciaProfesional']);

    Route::post('/personas/{personaId}/capacitaciones', [AcademicoController::class, 'storeCapacitacion']);
    Route::put('/capacitaciones/{id}', [AcademicoController::class, 'updateCapacitacion']);
    Route::delete('/capacitaciones/{id}', [AcademicoController::class, 'destroyCapacitacion']);

    Route::post('/personas/{personaId}/idiomas', [AcademicoController::class, 'storeIdioma']);
    Route::put('/idiomas/{id}', [AcademicoController::class, 'updateIdioma']);
    Route::delete('/idiomas/{id}', [AcademicoController::class, 'destroyIdioma']);

    Route::post('/personas/{personaId}/produccion-intelectual', [AcademicoController::class, 'storeProduccionIntelectual']);
    Route::put('/produccion-intelectual/{id}', [AcademicoController::class, 'updateProduccionIntelectual']);
    Route::delete('/produccion-intelectual/{id}', [AcademicoController::class, 'destroyProduccionIntelectual']);

    Route::post('/personas/{personaId}/reconocimientos', [AcademicoController::class, 'storeReconocimiento']);
    Route::put('/reconocimientos/{id}', [AcademicoController::class, 'updateReconocimiento']);
    Route::delete('/reconocimientos/{id}', [AcademicoController::class, 'destroyReconocimiento']);
});
