<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BanecoNotificationController;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/v1/baneco/notify-payment', [BanecoNotificationController::class, 'notify']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
