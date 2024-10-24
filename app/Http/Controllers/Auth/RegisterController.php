<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RandomGeneratorController as RandomPasswordGenerator;
use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use App\Mail\WelcomeEmail;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
    public function register(Request $request) 
    {
        $GeneratorController = new RandomPasswordGenerator();
        try {
            $passwordGenerado = $GeneratorController->generateRandomPassword();

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email',
                'password' => 'sometimes', //se genera automática
                'role_id' => 'required|exists:roles,id',
                // 'status' => 'nullable|boolean',

            ]);
            // Verificar si el correo ya está registrado
            if (User::where('email', $validatedData['email'])->exists()) {
                return response()->json([
                    'message' => 'Ya existe un usuario registrado con el correo electrónico proporcionado.',
                ], 400);
            }

            $validatedData['password'] = bcrypt($passwordGenerado);

            // Crear el usuario
            $user = User::create($validatedData);
            Profile::create([
                'user_id' => $user->id,
                'role_id' => $validatedData['role_id'],
            ]);
            UserDetail::create([
                'user_id' => $user->id,
                //'photo' => 'https://ui-avatars.com/api/?name=' . $user->name . '&color=7F9CF5&background=EBF4FF',
                //revisar el modo de foto
            ]);

            $roleName = Role::find($validatedData['role_id'])->name;
            
            // Enviar correo de bienvenida
            Mail::to($user->email)->send(new WelcomeEmail($user, $roleName, $passwordGenerado));
            
            // Devolver respuesta
            return response()->json([
                'message' => $roleName . ' registrado con éxito.',
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
}
