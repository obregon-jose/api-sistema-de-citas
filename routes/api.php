<?php

use Illuminate\Http\Request;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\AttentionQuoteController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TimeSlotController;
use App\Http\Middleware\CheckJwtToken;

/* RUTAS PUBLICAS (No requieren autenticación) */
Route::group(['prefix' => '/',], function () {
    Route::post('/register', [RegisterController::class, 'register']); // se registrar como cliente ->middleware('throttle:10,1')
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('reset-password/send-code', [ResetPasswordController::class, 'sendCode']);
    Route::post('reset-password/verify-code', [ResetPasswordController::class, 'verifyCode']);
    Route::put('reset-password/update-password', [ResetPasswordController::class, 'updatePassword']);
   
    /* -------------------- Show--Barbershop-Services */ 
    Route::get('/services',[ServiceController::class,'index']);

    // // Ruta para generar franjas horarias para una semana en una agenda específica
    // Route::post('{profileId}/horario/createTimeSlots', [TimeSlotController::class, 'generarFranjaSemana']);

    // Route::put('{profileId}/actualizar-franja', [TimeSlotController::class, 'actualizarFranja']);

    // Route::get('{profileId}/horario', [TimeSlotController::class, 'TimeSlotsBarber']);

    // Route::get('barbero/{profile_id}/disponibilidad/{fecha}', [TimeSlotController::class, 'obtenerFranjasPorFecha']);

    // Route::put('barbero/disponibilidad/', [TimeSlotController::class, 'ocuparFranja']); //Revisar ruta

    // Route::put('barbero/{profileId}/horarios/{fecha}', [TimeSlotController::class, 'actualizarHorarioPorFecha']);


    // // Ruta para actualizar una franja horaria específica
    // //Route::put('/horario/timeSlot/{id}', [TimeSlotController::class, 'actualizarFranja']);
    // // Ruta para eliminar una franja horaria específica
    // Route::delete('/horario/timeSlot/{id}', [TimeSlotController::class, 'eliminarFranja']);

    
});

/* ---------------- SOLO USUARIOS AUTENTICADOS --------------------*/
Route::group(['prefix' => '/', 'middleware' => [CheckJwtToken::class,/*'auth:sanctum'*/  /*<-NO USAR YA ESTA EN CHECHJWTTOKEN*/],], function () {
//     Route::post('/logout', [LogoutController::class, 'logout']);

    /* -------------------- Profile--Auth */
    Route::get('/user', function (Request $request) {
        $user = $request->user()->load(['detail']);
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'nickname' => $user->detail->nickname ?? null,
            'phone' => $user->detail->phone ?? null,
            'photo' => $user->detail->photo ?? null,
        ];
    });

    /* -------------------- Users */
    // Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users', [UserController::class, 'update']); //Actualiza el usuario autenticado
    Route::post('/users/photo', [UserController::class, 'updatePhoto']); //Actualiza el usuario autenticado

    // -------------------- Barbers--Profiles
//     Route::get('/barbers', [BarberController::class, 'index']);
//     // reservaciones activas[pendientes]
//     Route::get('/reservations-client/{id}', [ReservationController::class, 'showReservationsClient']);
    
    
/* ---------------- RUTAS CON ROLES --------------------*/

    // Requieren el rol 'cliente' 
//     Route::group(['middleware' => [ CheckRole::class . ':cliente']], function () {
//         // rutas reservas-cliente
//         Route::get('/reservations',[ReservationController::class,'index']);
//         // Route::get('/reservations/{id}',[ReservationController::class,'show']); //no
//         Route::post('/reservations',[ReservationController::class,'store']);
//         Route::put('/reservations/{id}',[ReservationController::class,'update']);
//         Route::delete('/reservations/{id}',[ReservationController::class,'destroy']);
        
//     });

        

    /* Requieren el rol 'peluquero' o 'administrador' */
    Route::group(['middleware' => [ CheckRole::class . ':peluquero,administrador']], function () {
        // Route::get('/services/{id}',[ServiceController::class,'show']); 
        Route::post('/services',[ServiceController::class,'store']);
        Route::put('/services/{id}',[ServiceController::class,'update']);
        Route::delete('/services/{id}',[ServiceController::class,'destroy']);
//         // rutas reservas-peluquero [atención]
//         Route::get('/attention-quotes',[AttentionQuoteController::class,'index']);
//         // Route::get('/attention-quotes/{id}',[AttentionQuoteController::class,'show']);
//         Route::post('/attention-quotes',[AttentionQuoteController::class,'store']);
//         Route::put('/attention-quotes/{id}',[AttentionQuoteController::class,'update']);
//         Route::delete('/attention-quotes/{id}',[AttentionQuoteController::class,'destroy']);
//         Route::get('/reservations-barber/{id}', [ReservationController::class, 'showReservationsBarber']);
    });

//     // Requieren el rol 'administrador' o 'root'
//     Route::group(['middleware' => [CheckRole::class . ':root,administrador']], function () {
        
//     });

//     // Requieren el rol 'dueño' o 'root'
//     Route::group(['middleware' => [CheckRole::class . ':root,dueño']], function () {
        
//     });

//     // Requieren el rol 'root'
//     Route::group(['middleware' => [CheckRole::class . ':root']], function () {
//         // rutas usuarios
//         Route::get('/users', [UserController::class, 'index']);
//         Route::delete('/users/{id}', [UserController::class, 'destroy']);
//     });

//     //
    Route::group(['middleware' => [CheckRole::class . ':root,dueño,administrador,peluquero']], function () {
        Route::get('/roles', [RoleController::class, 'index']);
    });
    

});


// Route::get('/barber-availability/{id}/{date}', [BarberController::class, 'getAvailability']);
// Route::get('/barber-agenda/{id}/{date}', [BarberController::class, 'getAgenda']);

// Route::post('/barber-agenda/{id}/{start_date}/{end_date}', [BarberController::class, 'updateAvailability']);
// Route::put('/agenda-reservation', [BarberController::class, 'updateReservation']);

// Route::get('/expired-reservations', [ReservationController::class, 'expiredReservations']);
// Route::put('/update-reservations', [ReservationController::class, 'updateReservations']);
Route::get('/', function () {
    echo "
    <html>
    <head>
        <script>
            function showMessage() {
                let messageElement = document.getElementById('message');
                let cargaElement = document.getElementById('carga');
                messageElement.innerText = 'Escaneando Usuario';
                
                let progress = 0;
                let interval = setInterval(function() {
                    progress += 1;
                    if (progress <= 100) {
                        cargaElement.innerText = 'Cargando... ' + progress + '%';
                    } else {
                        clearInterval(interval);
                        messageElement.innerText = 'Datos obtenidos.';
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