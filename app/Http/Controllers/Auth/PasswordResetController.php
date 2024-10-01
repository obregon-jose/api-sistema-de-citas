<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
        $request->validate([
            'email' => 'required|email'
        ]);
        
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'No hemos encentrado una cuanta asociada a este correo, por favor verifica el correo ingresado.'
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

        // Enviar el correo con el código
        Mail::to($request->email)->send(new ResetPasswordMail($user, $code));

        return response()->json([
            'message' => 'Hemos enviado un código de verificación a tu correo electrónico, por favor revisa tu bandeja de entrada.'
        ]);
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
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string'
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return response()->json([
                'status' => false,
                'message' => 'El código ingresado en invalido'
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'Código verificado', 
            'email' => $request->email
        ]);
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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // Actualizar la contraseña del usuario
        $user = User::where('email', $request->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        // Eliminar el token usado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Su contraseña se a actualizado con éxito.'
        ]);
    }
}
