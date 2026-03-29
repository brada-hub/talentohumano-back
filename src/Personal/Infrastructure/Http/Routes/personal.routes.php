<?php

use Illuminate\Support\Facades\Route;
use Src\Personal\Infrastructure\Http\Controllers\PersonaController;

Route::prefix('v1/personal')->middleware('auth:sanctum')->group(function () {
    Route::get   ('personas',      [PersonaController::class, 'index']);
    Route::post  ('personas',      [PersonaController::class, 'store']);
    Route::get   ('personas/{id}', [PersonaController::class, 'show']);
});
