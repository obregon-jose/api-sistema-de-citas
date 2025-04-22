<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
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

    public function passwordUpdate(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $user = User::find($request->user()->id);
            $user->password = bcrypt($validatedData['password']);
            $user->save();

            return response()->json([
                'message' => 'Contraseña actualizada con éxito.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
