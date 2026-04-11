<?php

use Illuminate\Support\Facades\Route;
use Src\Reportes\Infrastructure\Http\Controllers\ReportesController;

Route::prefix('v1/reportes')->middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [ReportesController::class, 'dashboard']);
    Route::get('/exportar', [ReportesController::class, 'export']);
});
