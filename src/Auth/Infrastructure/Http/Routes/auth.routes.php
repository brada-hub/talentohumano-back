<?php

use Illuminate\Support\Facades\Route;
use Src\Auth\Infrastructure\Http\Controllers\AuthController;

Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // SSO Management
        Route::prefix('sso')->group(function () {
            // Systems (Applications)
            Route::get('/systems', [\Src\Auth\Infrastructure\Http\Controllers\SsoManagementController::class, 'getSystems']);
            Route::post('/systems', [\Src\Auth\Infrastructure\Http\Controllers\SsoManagementController::class, 'storeSystem']);
            Route::put('/systems/{id}', [\Src\Auth\Infrastructure\Http\Controllers\SsoManagementController::class, 'updateSystem']);
            Route::delete('/systems/{id}', [\Src\Auth\Infrastructure\Http\Controllers\SsoManagementController::class, 'deleteSystem']);

            // Roles
            Route::get('/roles', [\Src\Auth\Infrastructure\Http\Controllers\SsoManagementController::class, 'getRoles']);
            Route::post('/roles', [\Src\Auth\Infrastructure\Http\Controllers\SsoManagementController::class, 'storeRole']);
            Route::put('/roles/{id}', [\Src\Auth\Infrastructure\Http\Controllers\SsoManagementController::class, 'updateRole']);

            // Permissions
            Route::get('/permissions', [\Src\Auth\Infrastructure\Http\Controllers\SsoManagementController::class, 'getPermissions']);
            Route::post('/permissions', [\Src\Auth\Infrastructure\Http\Controllers\SsoManagementController::class, 'storePermission']);

            // User-Role Assignments
            Route::get('/users', [\Src\Auth\Infrastructure\Http\Controllers\SsoManagementController::class, 'getUsers']);
            Route::post('/users/{id}/roles', [\Src\Auth\Infrastructure\Http\Controllers\SsoManagementController::class, 'assignRolesToUser']);
        });
    });
});
