<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Inicio de sesión de usuario",
     *     description="Inicia sesión con las credenciales del usuario",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="josh@example.com", description="Correo electrónico del usuario"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123!", description="Contraseña del usuario")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login exitoso."),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Correo electrónico o contraseña incorrectos, por favor revise sus credenciales.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Cuenta inactiva",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Tenemos problemas para iniciar sesión con su cuenta, comuníquese con el administrador.")
     *         )
     *     )
     * )
     */
    public function login(Request $request) 
    {
        try {
            $validatedData = $request->validate([
                "email" => "required|email",
                "password" => "required"
            ]);

            $user = User::withTrashed()->where("email", $request->email)->first();

            if (!empty($user)) {
                if ($user->trashed()) { //mostrar esto en caso de que se inactive una cuenta - solo casos especiales
                    return response()->json([
                        "message" => "Tenemos problemas para iniciar sesión con su cuenta, Comuníquese con el administrador.",
                    ], 403);
                }
                if (Hash::check($request->password, $user->password)) {
                    $token = $user->createToken("token")->accessToken;
                    $roleName = $user->profiles()->first()->role->name;
                    
                    return response()->json([
                        // "message" => "Login exitoso.",
                        "role" => $roleName,
                        // "user" => $user,
                        "token" => $token,
                        // "token_type" => "Bearer",
                        // "expires_at" => now()->addHours(1),
                    ], 200);
                } else {
                    return response()->json([
                        "message" => "Correo electrónico o contraseña incorrectos, por favor revise los datos ingresados",
                    ], 401);
                }
                
            } else {
                return response()->json([
                    // "message" => "No hemos encontrado un usuario registrado con este correo electrónico.",
                    "message" => "Correo electrónico o contraseña incorrectos, por favor revise los datos ingresados.",
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
