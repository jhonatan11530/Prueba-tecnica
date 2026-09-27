<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\QueryController;
use App\Http\Middleware\AuthTokenMiddleware;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

Route::middleware([AuthTokenMiddleware::class])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    Route::get('/paises', [LocationController::class, 'getPaises']);
    Route::get('/paises/{id}/ciudades', [LocationController::class, 'getCiudades']);
    
    Route::post('/consultas', [QueryController::class, 'hacerConsulta']);
    Route::get('/consultas/historial', [QueryController::class, 'historial']);
});
