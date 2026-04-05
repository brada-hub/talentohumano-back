<?php

use Illuminate\Support\Facades\Route;
use Src\Beneficios\Infrastructure\Http\Controllers\BeneficiosController;

Route::prefix('v1/beneficios')->group(function () {
    Route::get('/catalogs', [BeneficiosController::class, 'catalogs']);
    Route::get('/empleados/{empleadoId}/beneficiarios', [BeneficiosController::class, 'indexByEmpleado']);
    Route::post('/empleados/{empleadoId}/beneficiarios', [BeneficiosController::class, 'store']);
    Route::put('/beneficiarios/{id}', [BeneficiosController::class, 'update']);
    Route::delete('/beneficiarios/{id}', [BeneficiosController::class, 'destroy']);
});
