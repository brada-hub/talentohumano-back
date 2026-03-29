<?php

use Illuminate\Support\Facades\Route;
use Src\Beneficios\Infrastructure\Http\Controllers\BeneficiosController;

Route::prefix('v1/beneficios')->group(function () {
    Route::get('/catalogs', [BeneficiosController::class, 'catalogs']);
});
