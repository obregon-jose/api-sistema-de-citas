<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendResetCodeEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/password/send-reset-code",
     *     summary="Enviar código de restablecimiento",
     *    description="Enviar un código de verificación al correo electrónico del usuario",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Código de verificación enviado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hemos enviado un código de verificación a tu correo electrónico, por favor revisa tu bandeja de entrada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Correo no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No hemos encentrado una cuanta asociada a este correo, por favor verifica el correo ingresado.")
     *         )
     *     )
     * )
     */
    public function sendResetCode(Request $request)
    {
        $user = User::firstWhere('email', $request->email);
        if (!$user) {
            return response()->json([
                'message' => 'No hemos encentrado una cuanta asociada a este correo.'
            ], 404);
        }

        $code = rand(100000, 999999);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $code,
                'created_at' => now()
            ]
        );

        // Despachar el Job para enviar el correo en segundo plano
        SendResetCodeEmail::dispatch($user, $code);
        return response()->json([
            'message' => 'Hemos enviado un código de verificación a tu correo electrónico, por favor revisa tu bandeja de entrada.'
        ], 200);
    }

    /**
    * @OA\Post(
    *     path="/api/verify-reset-code",
    *     summary="Verificar código de restablecimiento",
    *     description="Verificar si el código de restablecimiento es válido",
    *     tags={"Auth"},
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             required={"email", "token"},
    *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
    *             @OA\Property(property="token", type="string", example="123456")
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Código verificado",
    *         @OA\JsonContent(
    *             @OA\Property(property="status", type="boolean", example=true),
    *             @OA\Property(property="message", type="string", example="Código verificado"),
    *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
    *         )
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Código inválido",
    *         @OA\JsonContent(
    *             @OA\Property(property="status", type="boolean", example=false),
    *             @OA\Property(property="message", type="string", example="El código ingresado en invalido")
    *         )
    *     )
    * )
    */

    public function verifyResetCode(Request $request)
    {
        $exists = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->exists();

        if (!$exists) {
            return response()->json([
                'message' => 'El código ingresado es inválido'
            ], 400);
        }

        return response()->json([
            // 'email' => $request->email
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/password/reset/update",
     *     summary="Actualizar la contraseña",
     *     description="Actualizar la contraseña de un usuario",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="NewPassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="NewPassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contraseña actualizada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Su contraseña se a actualizado con éxito.")
     *         )
     *     )
     * )
     */
    public function updatePassword(Request $request)
    {
        $exists = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->where('token', $request->token)
        ->exists();

        if (!$exists) {
            return response()->json([
                'message' => 'Por favor, solicite un código de restablecimiento.'
            ], 400);
        }

        //Actualizar la contraseña del usuario
        User::where('email', $request->email)->update(['password' => bcrypt($request->password)]);

        // Eliminar el token usado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Su contraseña se a actualizado con éxito.',
        ]);
    }
}
