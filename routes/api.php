<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\User\UserController;
use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;

Route::get('/', function () {
    echo "hola, bienvenido a la api";
});

// RUTAS PUBLICAS (No requieren autenticación)
Route::post('password/send-reset-code', [PasswordResetController::class, 'sendResetCode']);
Route::post('password/verify-reset-code', [PasswordResetController::class, 'verifyResetCode']);
Route::post('password/reset/update', [PasswordResetController::class, 'updatePassword']);
// limitar intentos de registro y login con middleware 'throttle' indicando los intentos permitidos,tiempo 
Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');
Route::post('/users', [UserController::class, 'store']); //ESTA EN AUTHCONTROLLER-reutilizando
// Route::post('/login', [AuthController::class, 'login']);
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');

// Perfil del usuario autenticado
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user()->with('profiles.role')->get();
});

// Grupo de rutas que solo requieren autenticación (Todos los roles)
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/logout', [LogoutController::class, 'logout']);
    //Route::post('/profile', [ProfileController::class, 'profile']); //esto debe der crup de profile
    
    // rutas usuarios
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']); //revisar - pacth
    
    // rutas servicios
    Route::get('/services',[ServiceController::class,'index']);

});

// Grupo de rutas que requieren el rol 'root'
Route::group(['middleware' => ['auth:api', CheckRole::class . ':root']], function () {
    // rutas usuarios
    
    Route::get('/users', [UserController::class, 'index']); //solo con permisos
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // revisar
});

// Grupo de rutas que requieren el rol 'cliente' ++root
Route::group(['middleware' => ['auth:api', CheckRole::class . ':root,cliente']], function () {
    
});

// Grupo de rutas que requieren el rol 'peluquero' ++root
Route::group(['middleware' => ['auth:api', CheckRole::class . ':root,peluquero']], function () {
    // rutas servicios
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
