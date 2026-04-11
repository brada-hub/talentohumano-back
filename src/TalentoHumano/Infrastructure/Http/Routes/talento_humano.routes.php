<?php

use Illuminate\Support\Facades\Route;
use Src\TalentoHumano\Infrastructure\Http\Controllers\EmpleadoController;
use Src\TalentoHumano\Infrastructure\Http\Controllers\TalentoHumanoController;
use Src\TalentoHumano\Infrastructure\Http\Controllers\LegajoController;
use Src\TalentoHumano\Infrastructure\Http\Controllers\CvController;
use Src\TalentoHumano\Infrastructure\Http\Controllers\ContratoController;

Route::prefix('v1/talento-humano')->group(function () {
    Route::get('/catalogs', [TalentoHumanoController::class, 'catalogs']);
    Route::get('/sedes', [TalentoHumanoController::class, 'getSedes']);
    Route::post('/sedes', [TalentoHumanoController::class, 'storeSede']);
    Route::put('/sedes/{id}', [TalentoHumanoController::class, 'updateSede']);
    Route::delete('/sedes/{id}', [TalentoHumanoController::class, 'deleteSede']);

    Route::get('/campus', [TalentoHumanoController::class, 'getCampus']);
    Route::post('/campus', [TalentoHumanoController::class, 'storeCampus']);
    Route::put('/campus/{id}', [TalentoHumanoController::class, 'updateCampus']);
    Route::delete('/campus/{id}', [TalentoHumanoController::class, 'deleteCampus']);

    Route::get('/niveles-jerarquicos', [TalentoHumanoController::class, 'getNivelesJerarquicos']);
    Route::post('/niveles-jerarquicos', [TalentoHumanoController::class, 'storeNivelJerarquico']);
    Route::put('/niveles-jerarquicos/{id}', [TalentoHumanoController::class, 'updateNivelJerarquico']);
    Route::delete('/niveles-jerarquicos/{id}', [TalentoHumanoController::class, 'deleteNivelJerarquico']);

    Route::get('/areas', [TalentoHumanoController::class, 'getAreas']);
    Route::post('/areas', [TalentoHumanoController::class, 'storeArea']);
    Route::put('/areas/{id}', [TalentoHumanoController::class, 'updateArea']);
    Route::delete('/areas/{id}', [TalentoHumanoController::class, 'deleteArea']);

    Route::get('/grupos-personal', [TalentoHumanoController::class, 'getGruposPersonal']);
    Route::post('/grupos-personal', [TalentoHumanoController::class, 'storeGrupoPersonal']);
    Route::put('/grupos-personal/{id}', [TalentoHumanoController::class, 'updateGrupoPersonal']);
    Route::delete('/grupos-personal/{id}', [TalentoHumanoController::class, 'deleteGrupoPersonal']);

    Route::get('/cargos', [TalentoHumanoController::class, 'getCargos']);
    Route::post('/cargos', [TalentoHumanoController::class, 'storeCargo']);
    Route::put('/cargos/{id}', [TalentoHumanoController::class, 'updateCargo']);
    Route::delete('/cargos/{id}', [TalentoHumanoController::class, 'deleteCargo']);

    Route::get('/puestos', [TalentoHumanoController::class, 'getPuestos']);
    Route::post('/puestos', [TalentoHumanoController::class, 'storePuesto']);
    Route::put('/puestos/{id}', [TalentoHumanoController::class, 'updatePuesto']);
    Route::delete('/puestos/{id}', [TalentoHumanoController::class, 'deletePuesto']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/empleados', [EmpleadoController::class, 'index']);
        Route::get('/stats', [EmpleadoController::class, 'stats']);
        Route::get('/empleados/buscar', [EmpleadoController::class, 'buscarPersona']);
        Route::post('/empleados', [EmpleadoController::class, 'store']);
        Route::get('/empleados/{id}', [EmpleadoController::class, 'show']);
        Route::put('/empleados/{id}', [EmpleadoController::class, 'update']);

        // Legajo Digital
        Route::get('/empleados/{id}/legajos', [LegajoController::class, 'index']);
        Route::post('/empleados/{id}/legajos', [LegajoController::class, 'upload']);
        Route::delete('/legajos/{id_doc}', [LegajoController::class, 'destroy']);

        // CV / Curriculum Vitae
        Route::get('/cv/{personaId}/descargar', [CvController::class, 'descargar']);
        Route::get('/cv/{personaId}/preview', [CvController::class, 'preview']);

        // Contratos - gestion y generacion
        Route::post('/empleados/{id}/contratos', [ContratoController::class, 'store']);
        Route::put('/empleados/{id}/contratos/{contratoId}', [ContratoController::class, 'update']);
        Route::patch('/empleados/{id}/contratos/{contratoId}/finalizar', [ContratoController::class, 'finalize']);
        Route::get('/empleados/{id}/contratos/{contratoId}/versiones', [ContratoController::class, 'versions']);
        Route::post('/empleados/{id}/contratos/{contratoId}/firmado', [ContratoController::class, 'uploadSigned']);
        Route::post('/empleados/{id}/contratos/plazo-fijo/preview', [ContratoController::class, 'previewPlazoFijo']);
        Route::post('/empleados/{id}/contratos/plazo-fijo/descargar', [ContratoController::class, 'descargarPlazoFijo']);
        Route::post('/empleados/{id}/contratos/indefinido/preview', [ContratoController::class, 'previewIndefinido']);
        Route::post('/empleados/{id}/contratos/indefinido/descargar', [ContratoController::class, 'descargarIndefinido']);
    });
});

// Verificación QR pública (sin auth)
Route::prefix('v1/cv')->group(function () {
    Route::get('/verificar/{personaId}', [CvController::class, 'verificar']);
});
