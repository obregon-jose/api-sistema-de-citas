<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserDetailController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;

Route::get('/', function () {
    echo "
    <html>
    <head>
        <script>
            function showMessage() {
                let messageElement = document.getElementById('message');
                let cargaElement = document.getElementById('carga');
                messageElement.innerText = 'Hackeo iniciando ';
                
                let progress = 0;
                let interval = setInterval(function() {
                    progress += 1;
                    if (progress <= 100) {
                        cargaElement.innerText = 'nnnn... ' + progress + '%';
                    } else {
                        clearInterval(interval);
                        messageElement.innerText = 'Hackeo completado';
                    }
                }, Math.floor(Math.random() * (350 - 1 + 1)) + 1);
            }
            window.onload = showMessage;
        </script>
    </head>
    <body>
        <div id='message'></div>
        <div id='carga'></div>
    </body>
    </html>
    ";
});

// RUTAS PUBLICAS (No requieren autenticación)
Route::group(['prefix' => '/',], function () {
    Route::post('password/send-reset-code', [PasswordResetController::class, 'sendResetCode']);
    Route::post('password/verify-reset-code', [PasswordResetController::class, 'verifyResetCode']);
    Route::post('password/reset/update', [PasswordResetController::class, 'updatePassword']);

    // limitar intentos de registro y login con middleware 'throttle' indicando los intentos permitidos,tiempo 
    Route::post('/users', [UserController::class, 'store'])->middleware('throttle:5,1'); // se registra como cliente
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
});

// ------------------AUTENTICACIÓN REQUERIDA
// Perfil del usuario autenticado
Route::get('/user', function (Request $request) {
    $user = $request->user()->load(['profiles.role', 'detail']);
    return $user;
})->middleware('auth:api');

Route::group(['middleware' => ['auth:api']], function () {
/* ---------------- SOLO USUARIOS AUTENTICADOS --------------------*/
    Route::post('/logout', [LogoutController::class, 'logout']);
    // usuarios
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']); //revisar - pacth
    // servicios
    Route::get('/services',[ServiceController::class,'index']);

    // detalles de usuario
    // Route::get('/user-details', [UserDetailController::class, 'index']); //NO, se obtiene desde users
    // Route::post('/user-details', [UserDetailController::class, 'store']); //NO, se crea desde users
    // Route::get('/user-details/{id}', [UserDetailController::class, 'show']); //NO, se obtiene desde users
    Route::put('/user-details/{id}', [UserDetailController::class, 'update']);
    //Route::delete('/user-details/{id}', [UserDetailController::class, 'destroy']); //NO, eliminación en cascada desde users

    /* ---------------- RUTAS CON ROLES --------------------*/
    // Requieren el rol 'cliente'
    Route::group(['middleware' => [ CheckRole::class . ':cliente']], function () {
        
    });
    // Requieren el rol 'peluquero'
    Route::group(['middleware' => [ CheckRole::class . ':peluquero']], function () {
        // rutas servicios
        Route::get('/services/{id}',[ServiceController::class,'show']); 
        Route::post('/services',[ServiceController::class,'store']);
        Route::put('/services/{id}',[ServiceController::class,'update']);
        Route::delete('/services/{id}',[ServiceController::class,'destroy']);

    });
    // Requieren el rol 'administrador'
    Route::group(['middleware' => [CheckRole::class . ':administrador']], function () {
        
    });
    // Requieren el rol 'dueño'
    Route::group(['middleware' => [CheckRole::class . ':dueño']], function () {
        
    });
    // Requieren el rol 'root'
    Route::group(['middleware' => [CheckRole::class . ':root']], function () {
        // rutas usuarios
        Route::get('/users', [UserController::class, 'index']); //solo con permisos
        Route::delete('/users/{id}', [UserController::class, 'destroy']); // revisar
        Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');  //puede seleccionar rol
        
    });
});
