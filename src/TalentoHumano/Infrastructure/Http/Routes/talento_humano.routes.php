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

        // Contratos - plantilla y preview
        Route::post('/empleados/{id}/contratos/plazo-fijo/preview', [ContratoController::class, 'previewPlazoFijo']);
        Route::post('/empleados/{id}/contratos/plazo-fijo/descargar', [ContratoController::class, 'descargarPlazoFijo']);
    });
});

// Verificación QR pública (sin auth)
Route::prefix('v1/cv')->group(function () {
    Route::get('/verificar/{personaId}', [CvController::class, 'verificar']);
});
