<?php

use App\Http\Controllers\AttentionQuoteController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserDetailController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\RestauranteController;
use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Models\Restaurante;

Route::get('/', function () {
    echo "
    <html>
    <head>
        <script>
            function showMessage() {
                let messageElement = document.getElementById('message');
                let cargaElement = document.getElementById('carga');
                messageElement.innerText = 'Hackeo iniciandoooo ';
                
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


Route::get('/pruebas', function () {
    echo "
    <html>
    <head>
        <script>
            function showMessage() {
                let messageElement = document.getElementById('message');
                let cargaElement = document.getElementById('carga');
                messageElement.innerText = 'prueba de hackeo ';
                
                let progress = 0;
                let interval = setInterval(function() {
                    progress += 1;
                    if (progress <= 100) {
                        cargaElement.innerText = 'nnnn... ' + progress + '%';
                    } else {
                        clearInterval(interval);
                        messageElement.innerText = 'prueba de Hackeo completado';
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

Route::get('/restaurantes/{id}',[RestauranteController::class,'show']); 
Route::post('/restaurante',[RestauranteController::class,'store']);
Route::put('/restaurantes/{id}',[RestauranteController::class,'update']);
Route::delete('/restaurantes/{id}',[RestauranteController::class,'destroy']);
Route::get('/restaurantes',[RestauranteController::class,'index']);

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
    // Route::put('/users/{id}', [UserController::class, 'update']); //revisar - se actualiza desde los detalles
    // detalles de usuario
    Route::put('/user-details/{id}', [UserDetailController::class, 'update']);
    // servicios
    Route::get('/services',[ServiceController::class,'index']);
    
    
    /* ---------------- RUTAS CON ROLES --------------------*/
    // Requieren el rol 'cliente'
    Route::group(['middleware' => [ CheckRole::class . ':cliente']], function () {
        // rutas reservas-cliente
        Route::get('/reservations',[ReservationController::class,'index']);
        // Route::get('/reservations/{id}',[ReservationController::class,'show']); //no
        Route::post('/reservations',[ReservationController::class,'store']);
        Route::put('/reservations/{id}',[ReservationController::class,'update']);
        Route::delete('/reservations/{id}',[ReservationController::class,'destroy']);
        
    });
    // Requieren el rol 'peluquero'
    Route::group(['middleware' => [ CheckRole::class . ':peluquero']], function () {
        // rutas servicios
        Route::get('/services/{id}',[ServiceController::class,'show']); 
        Route::post('/services',[ServiceController::class,'store']);
        Route::put('/services/{id}',[ServiceController::class,'update']);
        Route::delete('/services/{id}',[ServiceController::class,'destroy']);
        // rutas reservas-peluquero [atención]
        Route::get('/attention-quotes',[AttentionQuoteController::class,'index']);
        // Route::get('/attention-quotes/{id}',[AttentionQuoteController::class,'show']);
        Route::post('/attention-quotes',[AttentionQuoteController::class,'store']);
        Route::put('/attention-quotes/{id}',[AttentionQuoteController::class,'update']);
        Route::delete('/attention-quotes/{id}',[AttentionQuoteController::class,'destroy']);


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
