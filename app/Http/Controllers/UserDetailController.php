<?php

namespace App\Http\Controllers;

use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserDetailController extends Controller
{
    /**
 * @OA\Get(
 *     path="/user-details",
 *     tags={"UserDetails"},
 *     summary="Obtiene todos los detalles de usuario",
 *     @OA\Response(
 *         response=200,
 *         description="Lista de detalles de usuario obtenida con éxito",
 *         @OA\JsonContent(type="object", properties={
 *             @OA\Property(property="message", type="string", example="Detalles de usuario obtenidos con éxito."),
 *             @OA\Property(property="userDetails", type="array", @OA\Items(ref="#/components/schemas/UserDetail"))
 *         })
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
    public function index()
    { 
        $userDetails = UserDetail::all(); // Obtiene todos los detalles de usuario
        return response()->json([
            'userDetails' => $userDetails, // Usando plural
        ], 200); // Respuesta JSON
    }

    /**
 * @OA\Post(
 *     path="/user-details",
 *     tags={"UserDetails"},
 *     summary="Crea un nuevo detalle de usuario",
 *     description="Crea un nuevo registro de detalle de usuario y almacena su información en la base de datos.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="user_id", type="integer", example=1, description="ID del usuario"),
 *                 @OA\Property(property="nickname", type="string", example="Benildo", description="Apodo del usuario"),
 *                 @OA\Property(property="phone", type="string", example="3017829023", description="Número de teléfono del usuario"),
 *                 @OA\Property(property="photo", type="string", nullable=true, example=null, description="Ruta de la foto del usuario"),
 *                 @OA\Property(property="note", type="string", nullable=true, example="La cabra", description="Nota sobre el usuario")
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Detalle de usuario creado con éxito",
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="message", type="string", example="Detalle de usuario creado con éxito."),
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
 *                 @OA\Property(property="message", type="string", example="Ha ocurrido un error estupido. Por favor, inténtalo nuevamente más tarde."),
 *                 @OA\Property(property="error", type="string", example="Detalles del error aquí.")
 *             }
 *         )
 *     )
 * )
 */

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'nickname' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:15',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'note' => 'nullable|string',
            ]);

            $userDetail = userDetail::create($validatedData); // Crea un nuevo detalle de usuario

            return response()->json([
                'message' => 'Detalle de usuario creado con éxito.',
                'userDetail' => $userDetail,
            ], 201); // Respuesta JSON con código 201
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Ha ocurrido un error estupido. Por favor, inténtalo nuevamente más tarde.',
                'error' => $err->getMessage(),
            ], 400); // Respuesta JSON de error con código 400
        }
    }

    /**
 * @OA\Get(
 *     path="/user-details/{user_id}",
 *     tags={"UserDetails"},
 *     summary="Obtiene los detalles de un usuario específico",
 *     description="Obtiene los detalles de un usuario a través de su ID.",
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         description="ID del usuario",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Detalles del usuario obtenidos con éxito",
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="userDetail", ref="#/components/schemas/UserDetail")
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Usuario no encontrado",
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="message", type="string", example="Detalles del usuario no encontrado."),
 *                 @OA\Property(property="error", type="string", example="No se encontró el recurso solicitado.")
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
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

    public function show($user_id)
    {
        try {

            $userDetail = UserDetail::findOrFail($user_id);
            return response()->json([
                'userDetail' => $userDetail,
            ], 200);
        } catch (ModelNotFoundException $err) {
            return response()->json([
                'message' => 'Detalles del usuario no encontrado.',
                'error' => $err->getMessage(),
            ], 404);
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $err->getMessage(),
            ], 500); // Código 500 para errores internos
        }
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

    public function update(Request $request, UserDetail $userDetail)
    {
        try {

            $validatedDetail = $request->validate([
                'nickname' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:10',
                'photo' => 'nullable|string|max:255',
                'note' => 'nullable|string',
            ]);

            $userDetail->update($validatedDetail); // Actualiza el detalle de usuario
            return response()->json([
                'message' => 'Detalle de usuario actualizado con éxito.',
                'userDetail' => $userDetail,
            ], 200); // Respuesta JSON con el detalle actualizado
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $err->getMessage(),
            ], 400); // Respuesta JSON de error con código 400
        }
    }

    /**
 * @OA\Delete(
 *     path="/user-details/{id}",
 *     tags={"UserDetails"},
 *     summary="Elimina (desactiva) los detalles de un usuario",
 *     description="Elimina los detalles de un usuario específico a través de su ID.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID del detalle del usuario",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Detalles del usuario desactivados con éxito",
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="message", type="string", example="Los detalles han pasado a estar inactivo.")
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error interno del servidor",
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

    public function destroy($id)
    {
        try {
            $userDetail = UserDetail::findOrFail($id);
            $userDetail->delete();
            return response()->json([
                'message' => 'Los detalles han pasado a estar inactivo.',
            ], 200);
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $err->getMessage(),
            ], 500);
        }
    }
}
