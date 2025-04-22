<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BarberController;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Jobs\SendWelcomeEmail;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar un nuevo usuario con un rol específico",
     *     description="Registra un nuevo usuario en el sistema con un rol específico",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe", description="Nombre del usuario"),
     *             @OA\Property(property="email", type="string", format="email", example="josh@example.com", description="Correo electrónico del usuario"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123!", description="Contraseña del usuario. Debe contener al menos una letra minúscula, una mayúscula y un número."),
     *             @OA\Property(property="role_id", type="integer", example=1, nullable=true, description="ID del rol del usuario (opcional)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario registrado con éxito"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="role", type="string", example="cliente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde."),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="El campo nombre es obligatorio.")),
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="El campo correo electrónico es obligatorio.")),
     *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="El campo contraseña debe contener al menos una letra mayúscula.")),
     *                 @OA\Property(property="role_id", type="array", @OA\Items(type="string", example="El rol seleccionado no es válido."))
     *             )
     *         )
     *     )
     * )
     */
    public function register(RegisterUserRequest $request)
    {
        DB::beginTransaction();
        try {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);
    
            $user->detail()->create([
                'phone' => $request->phone,
            ]);
    
            
            $user->profiles()->create([
                'user_id' => $user->id,
            ]);
            DB::commit();
            
            $roleName = Role::find(1)->name;
            
            SendWelcomeEmail::dispatch($user, $roleName);
                        
        
            return response()->json([
                'message' => 'Registrado exitosamente',
            ], 201);
     
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

}
