<?php

use Illuminate\Support\Facades\Route;
use Src\Onboarding\Infrastructure\Http\Controllers\PortalOnboardingController;

/**
 * RUTAS PÚBLICAS DEL PORTAL
 */
Route::group(['prefix' => 'portal'], function () {
    Route::get('/token/{token}/validar', [PortalOnboardingController::class, 'validarToken']);
    Route::post('/verificar', [PortalOnboardingController::class, 'verificar']);
    Route::post('/personal', [PortalOnboardingController::class, 'guardarPersonal']);
    Route::post('/academico', [PortalOnboardingController::class, 'guardarAcademico']);
    Route::post('/completar', [PortalOnboardingController::class, 'completar']);
    Route::get('/archivo/{filename}', [PortalOnboardingController::class, 'mostrarArchivo'])->where('filename', '.*');
});

/**
 * RUTAS ADMINISTRATIVAS
 */
Route::group(['prefix' => 'onboarding', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/status', [\Src\Onboarding\Infrastructure\Http\Controllers\AdminOnboardingController::class, 'getStatus']);
    Route::post('/toggle', [\Src\Onboarding\Infrastructure\Http\Controllers\AdminOnboardingController::class, 'toggleStatus']);
    Route::post('/generar-token', [\Src\Onboarding\Infrastructure\Http\Controllers\AdminOnboardingController::class, 'generarToken']);
});
