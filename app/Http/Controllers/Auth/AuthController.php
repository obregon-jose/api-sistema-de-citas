<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request) 
    {
        try {
            // Validar la solicitud
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users,email',
                'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
                'role_id' => 'sometimes|exists:roles,id',
                // 'status' => 'nullable|boolean',
            ]);

            // Verificar si hay un usuario autenticado
            $token = $request->bearerToken();
            $authenticatedUser = $token ? Auth::guard('api')->user() : null;
            
            $defaultRoleId = 1;
            if ($authenticatedUser) {
                // Verificar si el usuario autenticado tiene el rol de 'root'
                if ($authenticatedUser->profiles()->whereHas('role', function($query) {
                    $query->where('name', 'root');
                })->exists()) {
                    $defaultRoleId = $validatedData['role_id'] ?? $defaultRoleId;
                    $passwordGenerado = $this->generateRandomPassword();
                    $validatedData['password'] = $passwordGenerado;
                }
            }

            // Encriptar la contraseña
            $validatedData['password'] = bcrypt($validatedData['password']);

            // Crear el usuario
            $user = User::create($validatedData);
            
            Profile::create([
                'user_id' => $user->id,
                'role_id' => $defaultRoleId,
            ]);

            $roleName = Role::find($defaultRoleId)->name;
            
            // Enviar correo de bienvenida
            Mail::to($user->email)->send(new WelcomeEmail($user, $roleName, $passwordGenerado));
            
            // Devolver respuesta
            return response()->json([
                
                'message' => 'Usuario registrado con éxito',
                'user' => $user,
                'role' => $roleName,
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
        try {
            $validatedData = $request->validate([
                "email" => "required|email",
                "password" => "required"
            ]);

            $user = User::where("email", $request->email)->first();

            if (!empty($user)) {
                
                    if (Hash::check($request->password, $user->password)) {
                        // $user->update(['failed_attempts' => 0]);
                        $token = $user->createToken("token")->accessToken;
                        return response()->json([
                            "message" => "Login exitoso.",
                            "user" => $user,
                            "token" => $token,
                            // "token_type" => "Bearer",
                            // "expires_at" => now()->addHours(1),
                        ], 200);
                    } else {
                        // $this->handleFailedLogin($user);
                        // return $this->sendFailedLoginResponse($user);
                    }
                
            } else {
                return response()->json([
                    "message" => "Usuario no encontrado.",
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request) 
    {
        try {
            $request->user()->token()->revoke();
            return response()->json([
                "message" => "Sesión cerrada con éxito",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //Generar contraseñas aleatorias
    public function generateRandomPassword($length = 12) {
        $characters = implode('', array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'), str_split('!@#$%^&*()-_=+[]{}|;:,.<>?')));
        $charactersLength = strlen($characters);
        $randomPassword = '';
        for ($i = 0; $i < $length; $i++) {
            $randomPassword .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomPassword;
    }
}


/*
    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                "email" => "required|email",
                "password" => "required"
            ]);

            $user = User::withTrashed()->where("email", $request->email)->first();

            if (!empty($user)) {
                if (!$user->trashed()) {
                    if (Hash::check($request->password, $user->password)) {
                        $user->update(['failed_attempts' => 0]);
                        $token = $user->createToken("token")->accessToken;
                        return response()->json([
                            // "message" => "Login exitoso.",
                            "user" => $user,
                            "token" => $token,
                            // "token_type" => "Bearer",
                            // "expires_at" => now()->addHours(1),
                        ], 200);
                    } else {
                        $this->handleFailedLogin($user);
                        return $this->sendFailedLoginResponse($user);
                    }
                } else {
                    return response()->json([
                        "message" => "Su cuenta está inactiva, Comuníquese con el administrador.",
                    ], 403);
                }
            } else {
                return response()->json([
                    "message" => "Usuario no encontrado.",
                ], 401);
            }
        } catch (\Exception $err) {
            return response()->json([
                "message" => $err->getMessage(),
                "error" => "Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.",
            ], 500);
        }
    }

    // public function logout(Request $request)
    // {
    //     $request->user()->currentAccessToken()->delete();

    //     return response()->json(['message' => 'Sesión cerrada correctamente']);
    // }

    // Métodos Bloqueo de cuenta
    protected function handleFailedLogin($user)
    {
        $user->increment('failed_attempts');
        $maxFailedAttempts = 5;

        if ($user->failed_attempts >= $maxFailedAttempts) {
            $user->delete();
            //$user->update(['status' => false]);
        }
    }

    protected function sendFailedLoginResponse($user)
    {
        $maxFailedAttempts = 5;

        if ($user->failed_attempts >= $maxFailedAttempts) {
            return response()->json([
                "message" => "Su cuenta ha sido bloqueada debido a múltiples intentos fallidos de inicio de sesión. Comuníquese con el administrador.",
            ], 429);
        }

        return response()->json([
            "message" => "Contraseña incorrecta. Te quedan " . ($maxFailedAttempts - $user->failed_attempts) . " intentos antes de que tu cuenta sea bloqueada."
        ], 401);
    }

    
    
    */