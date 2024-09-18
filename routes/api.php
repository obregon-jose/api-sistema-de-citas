<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

// 'role:root'
Route::group(['middleware' => ['auth:api', ], ], function () {
    Route::get('/s', function () {

        $user = Auth::user();

        return response()->json([
            'user' => $user
        ]);

        // // Verificar si el usuario tiene el rol "admin"
        // if ($user->hasRole('root')) {
        //     return response()->json(['message' => 'No tienes permiso para acceder a esta ruta'], 403);
        // }else{
            return response()->json([
                        'message' => 'FUNCIONAR'
                    ]);
        // }

    });
 });
