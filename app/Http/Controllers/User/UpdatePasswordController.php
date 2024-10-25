<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UpdatePasswordController extends Controller
{
    //
    public function updatePassword(Request $request)
    {
        //Actualizar la contraseña del usuario
        // User::where('email', $request->email)->update(['password' => bcrypt($request->password)]);     

        // return response()->json([
        //     'message' => 'Su contraseña se a actualizado.',
        //     'success' => true,
        // ]);
    }
}
