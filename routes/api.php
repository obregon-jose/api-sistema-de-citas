<?php

use Illuminate\Http\Request;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserDetailController;
use App\Http\Controllers\User\UpdatePasswordController;
use App\Http\Controllers\AttentionQuoteController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\RoleController;


// RUTAS PUBLICAS (No requieren autenticación)
Route::group(['prefix' => '/',], function () {
    Route::post('password/send-reset-code', [PasswordResetController::class, 'sendResetCode']);
    Route::post('password/verify-reset-code', [PasswordResetController::class, 'verifyResetCode']);
    Route::post('password/reset-update', [PasswordResetController::class, 'updatePassword']);

    Route::post('/users', [UserController::class, 'store']); // se registra como cliente
    Route::post('/login', [LoginController::class, 'login']);

    // Mostrar servicios
    Route::get('/services',[ServiceController::class,'index']);

    Route::post('/subir-imagen/{id}', [UserDetailController::class, 'uploadImage']); //revisar
});

// ------------------AUTENTICACIÓN REQUERIDA
// Perfil
Route::get('/user', function (Request $request) {
    // $user = $request->user()->load(['profiles.role', 'detail']);
    $user = $request->user()->load(['detail']);
    return $user;

})->middleware('auth:sanctum');


Route::group(['prefix' => '/', 'middleware' => 'auth:sanctum',], function () {
/* ---------------- SOLO USUARIOS AUTENTICADOS --------------------*/
    Route::post('/logout', [LogoutController::class, 'logout']);
    // usuarios
    Route::get('/users/{id}', [UserController::class, 'show']);
    // Route::put('/users/{id}', [UserController::class, 'update']); //revisar - se actualiza desde los detalles
    // detalles de usuario
    Route::put('/user-details/{id}', [UserDetailController::class, 'update']);
    
/* ---------------- RUTAS CON ROLES --------------------*/

    // Requieren el rol 'cliente' o root
    Route::group(['middleware' => [ CheckRole::class . ':root,client']], function () {
        // rutas reservas-cliente
        Route::get('/reservations',[ReservationController::class,'index']);
        // Route::get('/reservations/{id}',[ReservationController::class,'show']); //no
        Route::post('/reservations',[ReservationController::class,'store']);
        Route::put('/reservations/{id}',[ReservationController::class,'update']);
        Route::delete('/reservations/{id}',[ReservationController::class,'destroy']);
        
    });
    // Requieren el rol 'peluquero' o root
    Route::group(['middleware' => [ CheckRole::class . ':root,barber']], function () {
        // rutas servicios
        Route::get('/services/{id}',[ServiceController::class,'show']); 
        Route::post('/services',[ServiceController::class,'store']);
        Route::put('/services/{id}',[ServiceController::class,'update']);
        Route::delete('/services/{id}',[ServiceController::class,'destroy']);
        //Ruta para obtener los peluqueros
        Route::get('/barbers', [BarberController::class, 'index']);
        
        // rutas reservas-peluquero [atención]
        Route::get('/attention-quotes',[AttentionQuoteController::class,'index']);
        // Route::get('/attention-quotes/{id}',[AttentionQuoteController::class,'show']);
        Route::post('/attention-quotes',[AttentionQuoteController::class,'store']);
        Route::put('/attention-quotes/{id}',[AttentionQuoteController::class,'update']);
        Route::delete('/attention-quotes/{id}',[AttentionQuoteController::class,'destroy']);


    });
    // Requieren el rol 'administrador' o root
    Route::group(['middleware' => [CheckRole::class . ':root,admin']], function () {
        Route::post('/register', [RegisterController::class, 'register']);  //puede seleccionar rol
    });
    // Requieren el rol 'dueño' o root
    Route::group(['middleware' => [CheckRole::class . ':root,owner']], function () {
        
    });
    // Requieren el rol 'root'
    Route::group(['middleware' => [CheckRole::class . ':root']], function () {
        //
        Route::get('/roles', [RoleController::class, 'index']);
        // rutas usuarios
        Route::get('/users', [UserController::class, 'index']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        
        
    });
});

Route::get('/', function () {
    echo "
    <html>
    <head>
        <script>
            function showMessage() {
                let messageElement = document.getElementById('message');
                let cargaElement = document.getElementById('carga');
                messageElement.innerText = 'Hackeo iniciando';
                
                let progress = 0;
                let interval = setInterval(function() {
                    progress += 1;
                    if (progress <= 100) {
                        cargaElement.innerText = 'Cargando... ' + progress + '%';
                    } else {
                        clearInterval(interval);
                        messageElement.innerText = 'Hackeo completado, Datos obtenidos.';
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