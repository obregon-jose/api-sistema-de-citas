<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');


// Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
// Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);


// Route::group(['middleware' => ['auth', 'role:root']], function () {
//     Route::get('/s', function () {
//         return response()->json([
//             'message' => 'Hello, this is a simple JSON response!'
//         ]);
//     });

//     // Route::post('/admin/dashboard', [AdminController::class, 'index']);
// });

