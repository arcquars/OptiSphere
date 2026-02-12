<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BanecoNotificationController;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);

/**
 * Rutas Protegidas por Sanctum
 * Todas las rutas dentro de este grupo requieren el header 'Authorization: Bearer <token>'
 */
Route::middleware('auth:sanctum')->group(function () {
    
    // Ruta de notificación del banco ahora protegida
    Route::post('/v1/baneco/notify-payment', [BanecoNotificationController::class, 'notify']);

    // Ruta de ejemplo para obtener datos del usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

/*
Route::post('/v1/baneco/notify-payment', [BanecoNotificationController::class, 'notify']);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/