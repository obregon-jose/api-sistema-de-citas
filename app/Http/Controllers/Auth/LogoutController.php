<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{    
    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Cerrar sesión de usuario",
     *     description="Cierra la sesión del usuario autenticado",
     *     tags={"Auth"},
     *     @OA\Response(
     *         response=200,
     *         description="Sesión cerrada con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Sesión cerrada con éxito")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error inesperado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function logout(Request $request) 
    {
        $user = Auth::user();
        $user->tokens()->delete();
        return response()->json([
            // 'success' => true,
            // 'message' => 'Sesión cerrada'
        ], 200);
    }
}
