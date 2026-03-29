<?php

use Illuminate\Support\Facades\Route;
use Src\Geo\Infrastructure\Http\Controllers\GeoController;

Route::prefix('v1/geo')->group(function () {
    Route::get('/paises', [GeoController::class, 'paises']);
    Route::get('/paises/{paisId}/departamentos', [GeoController::class, 'departamentos']);
    Route::get('/departamentos/{departamentoId}/ciudades', [GeoController::class, 'ciudades']);
    Route::get('/ciudades', [GeoController::class, 'searchCiudades']);
    Route::get('/nacionalidades', [GeoController::class, 'nacionalidades']);
});
