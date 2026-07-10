<?php

use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\StopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/stops', [StopController::class, 'index']);
Route::get('/stops/{stop}', [StopController::class, 'show']);

Route::get('/routes', [RouteController::class, 'index']);
Route::get('/routes/{route}', [RouteController::class, 'show']);
