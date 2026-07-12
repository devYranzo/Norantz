<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\StopController;
use Illuminate\Support\Facades\Route;

// Catálogo estático (bundle completo, versionado)
Route::get('/catalog/meta', [CatalogController::class, 'meta']);
Route::get('/catalog', [CatalogController::class, 'show']);

// Detalle de una parada (rutas reales calculadas on-demand)
Route::get('/stops/{stop}', [StopController::class, 'show']);

// Patrones de ruta (con shape/coordinates) para dibujar líneas
Route::get('/routes', [RouteController::class, 'index']);
Route::get('/routes/{route}', [RouteController::class, 'show']);

