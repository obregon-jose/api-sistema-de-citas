<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// RUTAS PUBLICAS (No requieren autenticación)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Grupo de rutas que solo requieren autenticación (Todos los roles)
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

//NOTA: se puede agrupar por petición donde cada rol tiene acceso a peticiones http especificas (roles de CRUD)

// Grupo de rutas que requieren el rol 'root'
Route::group(['middleware' => ['auth:api', CheckRole::class . ':root']], function () {
    Route::get('/p', function () {return response()->json(['message' => 'Permitido']);});
});

// Grupo de rutas que requieren el rol 'cliente' ++root
Route::group(['middleware' => ['auth:api', CheckRole::class . ':root,cliente']], function () {
    
});

// Grupo de rutas que requieren el rol 'peluquero' ++root
Route::group(['middleware' => ['auth:api', CheckRole::class . ':root,peluquero']], function () {
    Route::get('/d', function () {return response()->json(['message' => 'Denegación']);});
});

// ADICIONALES
Route::group(['middleware' => ['auth:api', CheckRole::class . ':root,administrador']], function () {
    
});

Route::group(['middleware' => ['auth:api', CheckRole::class . ':root,dueño']], function () {
    
});
