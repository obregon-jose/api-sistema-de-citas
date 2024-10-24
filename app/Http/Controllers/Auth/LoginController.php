<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken("token")->accessToken;
            $role = $user->profiles()->first()->role->name;

            return response()->json(['token' => $token, 'role' => $role], 200);
        }

        return response()->json(['message' => 'Correo electrónico o contraseña incorrectos.'], 401);
    }

    //Con eliminación suave
    public function login2(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Encontrar el usuario (incluyendo los eliminados temporalmente)
        $user = User::withTrashed()->where('email', $credentials['email'])->first();
        // $user = User::withTrashed()->where("email", $request->email)->first();

        if ($user) {
            if ($user->trashed()) {
                return response()->json([
                    'message' => 'Su cuenta está desactivada.'
                ], 403);
            }

            // Intentar autenticación
            if (Auth::attempt($credentials)) {
                $token = $user->createToken('token')->accessToken;
                $role = $user->profiles()->first()->role->name;

                return response()->json([
                    'token' => $token,
                    'role' => $role
                ], 200);
            }
        }

        // Si las credenciales son incorrectas o el usuario no existe
        return response()->json(['message' => 'Correo electrónico o contraseña incorrectos.'], 401);
    }

}
