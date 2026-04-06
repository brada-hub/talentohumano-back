<?php

use Illuminate\Support\Facades\Route;
use Src\Recordatorios\Infrastructure\Http\Controllers\RecordatoriosController;

Route::prefix('v1/recordatorios')->middleware('auth:sanctum')->group(function () {
    Route::get('/resumen', [RecordatoriosController::class, 'resumen']);
    Route::post('/cumpleanios/{empleadoId}/enviar', [RecordatoriosController::class, 'enviarCumpleanios']);
});
