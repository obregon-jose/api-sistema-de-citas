<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;

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

    }

    public function logout(Request $request) 
    {
        
    }
}
