<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;

// RUTAS PUBLICAS (No requieren autenticación)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// Grupo de rutas que solo requieren autenticación (Todos los roles)
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::put('/user/{id}', [UserController::class, 'update']); //revisar
    // rutas servicios
    Route::get('/services',[ServiceController::class,'index']);

});

//NOTA: se puede agrupar por petición donde cada rol tiene acceso a peticiones http especificas (roles de CRUD)

// Grupo de rutas que requieren el rol 'root'
Route::group(['middleware' => ['auth:api', CheckRole::class . ':root']], function () {
    
    Route::post('/user', [UserController::class, 'store']); //ESTA EN AUTH
    Route::get('/user', [UserController::class, 'index']); //solo con permisos
    Route::delete('/user/{id}', [UserController::class, 'destroy']); // revisar
});

// Grupo de rutas que requieren el rol 'cliente' ++root
Route::group(['middleware' => ['auth:api', CheckRole::class . ':root,cliente']], function () {
    
});

// Grupo de rutas que requieren el rol 'peluquero' ++root
Route::group(['middleware' => ['auth:api', CheckRole::class . ':root,peluquero']], function () {
    
    Route::get('/services/{id}',[ServiceController::class,'show']); 
    Route::post('/services',[ServiceController::class,'store']);
    Route::put('/services/{id}',[ServiceController::class,'update']);
    Route::delete('/services/{id}',[ServiceController::class,'destroy']);

});

// ADICIONALES
Route::group(['middleware' => ['auth:api', CheckRole::class . ':root,administrador']], function () {
    
});

Route::group(['middleware' => ['auth:api', CheckRole::class . ':root,dueño']], function () {
    
});
