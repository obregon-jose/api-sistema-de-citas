<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function register(Request $request) 
    {
        try {
            // Validar la solicitud
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users,email',
                'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
                'role_id' => 'nullable|exists:roles,id',
                // 'status' => 'nullable|boolean',
            ]);

            // Encriptar la contraseña
            $validatedData['password'] = bcrypt($validatedData['password']);

            // Crear el usuario
            $user = User::create($validatedData);
            
            // Asignar el rol al usuario
            Profile::create([
                'user_id' => $user->id,
                'role_id' => $request->role_id ?? 1,// Establecer un valor por defecto para 'role_id' si no está presente
            ]);

            // Devolver respuesta
            return response()->json([
                'message' => 'Usuario registrado con éxito',
                'user' => $user,
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function login(Request $request) 
    {
        // try {
        //     $validatedData = $request->validate([
        //         "email" => "required|email",
        //         "password" => "required"
        //     ]);

        //     $user = User::where("email", $request->email)->first();

        //     if (!empty($user)) {
                
        //             if (Hash::check($request->password, $user->password)) {
        //                 // $user->update(['failed_attempts' => 0]);
        //                 $token = $user->createToken("token")->accessToken;
        //                 return response()->json([
        //                     "message" => "Login exitoso.",
        //                     "user" => $user,
        //                     "token" => $token,
        //                     // "token_type" => "Bearer",
        //                     // "expires_at" => now()->addHours(1),
        //                 ], 200);
        //             } else {
        //                 // $this->handleFailedLogin($user);
        //                 // return $this->sendFailedLoginResponse($user);
        //             }
                
        //     } else {
        //         return response()->json([
        //             "message" => "Usuario no encontrado.",
        //         ], 401);
        //     }
        // } catch (\Exception $err) {
        //     return response()->json([
        //         "message" => $err->getMessage(),
        //         "error" => "Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.",
        //     ], 500);
        // }
    }

    public function logout(Request $request) 
    {
        try {
            $request->user()->token()->revoke();
            return response()->json([
                "message" => "Sesión cerrada con éxito",
            ], 200);
        } catch (\Exception $err) {
            return response()->json([
                "message" => $err->getMessage(),
                "error" => "Error al cerrar sesión",
            ], 500);
        }
    }
}
