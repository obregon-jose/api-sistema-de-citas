<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// RUTAS PUBLICAS (No requieren autenticación o roles)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Grupo de rutas que solo requieren autenticación
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

//NOTA: se puede agrupar pot petición donde cada rol tiene acceso a peticiones http especificas

// Grupo de rutas que requieren el rol 'admin'
Route::group(['middleware' => ['auth:api', CheckRole::class . ':root']], function () {
    
});

// Grupo de rutas que requieren el rol 'peluquero'
Route::group(['middleware' => ['auth:api', CheckRole::class . ':peluquero']], function () {
    Route::get('/ss', function () {
        return response()->json([
            'message' => 'Permitido'
        ]);
    });
});

// Grupo de rutas que requieren el rol 'cliente'
Route::group(['middleware' => ['auth:api', CheckRole::class . ':cliente']], function () {
    Route::get('/s', function () {
        return response()->json([
            'message' => 'Denegación'
        ]);
    });
});