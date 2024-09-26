<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\RandomGeneratorController as RandomPasswordGenerator;
use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use App\Mail\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar un nuevo usuario",
     *     description="Registra un nuevo usuario en el sistema",
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
            // Validar la solicitud
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:users,email',
                'password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
                'role_id' => 'nullable|exists:roles,id',
                // 'status' => 'nullable|boolean',
            ]);

            // Verificar si hay un usuario autenticado
            $token = $request->bearerToken();
            $authenticatedUser = $token ? Auth::guard('api')->user() : null;
            
            $defaultRoleId = 1;
            if ($authenticatedUser) {
                // Verificar si el usuario autenticado tiene el rol de 'root'
                //NOTA: el error em profile es porque no esta detectando la función pero funciona normal el código
                if ($authenticatedUser->profiles()->whereHas('role', function($query) {
                    $query->where('name', 'root');
                })->exists()) {
                    $defaultRoleId = $validatedData['role_id'] ?? $defaultRoleId;
                    $passwordGenerado = $GeneratorController->generateRandomPassword();
                    $validatedData['password'] = $passwordGenerado;
                }
            }

            // Encriptar la contraseña
            $validatedData['password'] = bcrypt($validatedData['password']);

            // Crear el usuario
            $user = User::create($validatedData);
            
            Profile::create([
                'user_id' => $user->id,
                'role_id' => $defaultRoleId,
            ]);

            $roleName = Role::find($defaultRoleId)->name;
            
            // Enviar correo de bienvenida
            Mail::to($user->email)->send(new WelcomeEmail($user, $roleName, $passwordGenerado ?? ''));
            
            // Devolver respuesta
            return response()->json([
                
                'message' => 'Usuario registrado con éxito',
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
