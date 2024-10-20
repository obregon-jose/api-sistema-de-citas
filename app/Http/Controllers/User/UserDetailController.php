<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserDetailController extends Controller
{

    public function index()
    { 
        
    }

    public function store(Request $request)
    {

    }

    public function show($user_id)
    {

    }

    /**
     * @OA\Put(
     *     path="/user-details/{id}",
     *     tags={"UserDetails"},
     *     summary="Actualiza los detalles de un usuario",
     *     description="Actualiza la información de un usuario a través de su ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del detalle del usuario",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="nickname", type="string", nullable=true, example="Benildo", description="Apodo del usuario"),
     *                 @OA\Property(property="phone", type="string", nullable=true, example="3017829023", description="Número de teléfono del usuario"),
     *                 @OA\Property(property="photo", type="string", nullable=true, example="photos/user1.jpg", description="Ruta de la foto del usuario"),
     *                 @OA\Property(property="note", type="string", nullable=true, example="La cabra", description="Nota sobre el usuario")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalle de usuario actualizado con éxito",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Detalle de usuario actualizado con éxito."),
     *                 @OA\Property(property="userDetail", ref="#/components/schemas/UserDetail")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error en la solicitud o validación",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde."),
     *                 @OA\Property(property="error", type="string", example="Detalles del error aquí.")
     *             }
     *         )
     *     )
     * )
     */

    public function update(Request $request, $user_id)
    {
        try {
            $userDetail = UserDetail::where('user_id', $user_id)->firstOrFail();

            $validatedDetail = $request->validate([
                'name' => 'nullable|string|max:255',
                'nickname' => 'nullable|string|max:255',
                'phone' => 'nullable|string|min:8|max:10',
                'photo' => 'nullable|string|max:255',
                'note' => 'nullable|string',
            ]);

            $userDetail->update($validatedDetail); // Actualiza el detalle de usuario

            // Actualiza el nombre en la tabla User
            User::where('id', $user_id)->update(['name' => $validatedDetail['name']]);
            $user = User::find($user_id);

            return response()->json([
                'message' => 'Perfil actualizado con éxito.',
                'user' => $user,
                'userDetail' => $userDetail,
            ], 200); 
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $err->getMessage(),
            ], 400); 
        }
    }

    public function destroy($user_id)
    {

    }
}
