<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Obtener lista de usuarios",
     *     tags={"Usuarios"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autorizado"
     *     )
     * )
     */
    public function index()
    {
        //
        try {

            //$users = User::paginate(10);// Usar paginación en lugar de cargar todos los usuarios

            // Obtener solo los usuarios activos
            $users = User::with('profiles.role')->get();
            // Obtener solo los usuarios eliminados-Inactivos
            // $deletedUsers = User::onlyTrashed()->get();
            // Obtener todos los usuarios, incluidos los eliminados
            //$deletedUsers = User::withTrashed()->get();

            return response()->json([
                'users' => $users,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // FUNCION EN AUTHCONTROLLER
        $authController = new AuthController();
        $registerUser = $authController->register($request);
        return $registerUser;
    }


    public function show($id)
    {
        //
        try {
            $user = User::with('profiles.role')->findOrFail($id);
            return response()->json([
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        try {
            $user = User::findOrFail($id);
            $validatedUser = $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'password' => 'nullable|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
            ]);

            if ($request->filled('password')) {
                $validatedUser['password'] = bcrypt($validatedUser['password']);
            }
            
            $user->update($validatedUser);

            return response()->json([
                'message' => 'Usuario actualizado exitosamente.',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        try {
            $user = User::findOrFail($id);
            
            $user->delete(); //se debe es actualizar el estado del perfil
            // $user->update(['status' => false]);

            return response()->json([
                'message' => 'El usuario ha pasado a estar inactivo.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
