<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Jobs\SendWelcomeEmail;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Listar todos los usuarios",
     *     description="Obtiene una lista de todos los usuarios activos",
     *     tags={"User"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios obtenida con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="users", type="array", @OA\Items(type="object"))
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
    public function index()
    {
        //
        try {

            //$users = User::paginate(10);// Usar paginación en lugar de cargar todos los usuarios

            // Obtener solo los usuarios activos
            // $users = User::with(['profiles.role', 'detail'])->get();
            $users = User::with(['detail'])->get();
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
     * @OA\Post(
     *     path="/api/users",
     *     summary="Crear un nuevo usuario",
     *     description="Registra un nuevo usuario en el sistema",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe", description="Nombre del usuario"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com", description="Correo electrónico del usuario"),
     *             @OA\Property(property="password", type="string", format="password", example="Password123!", description="Contraseña del usuario. Debe contener al menos una letra minúscula, una mayúscula y un número."),
     *             @OA\Property(property="role_id", type="integer", example=2, nullable=true, description="ID del rol del usuario (opcional)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario registrado con éxito"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="role", type="string", example="admin")
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
    public function store(AddUserRequest $request) 
    {
        DB::beginTransaction();
        try {
            $passwordDecrypted = $request->password;
            if (!$request->password) {
                $passwordDecrypted = Str::random(10);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($passwordDecrypted),
            ]);
            $user->detail()->create([]);
            
            $user->profiles()->create([
                'role_id' => $request->role_id,
            ]);

            DB::commit();

            $roleName = Role::find($request->role_id)->name;

            if ($roleName == 'peluquero') {
                //$availabilityController = new BarberController();
                //$availabilityController->createDefaultAvailability($user->id);
                // CAMBIAR ESTO A SEGUNDO PLANO
                //CreateAvailability::dispatch($user->id);
            }
            
            // Enviar correo de bienvenida
            SendWelcomeEmail::dispatch($user, $roleName);
            
            // Devolver respuesta
            return response()->json([
                'message' => $roleName . ' registrado.',
                'password' => $passwordDecrypted,
            ], 201);
 
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Obtener un usuario por ID",
     *     description="Obtiene la información de un usuario específico",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $user = User::with(['detail'])->findOrFail($id);
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nickname' => $user->detail->nickname ?? null,
                'phone' => $user->detail->phone ?? null,
                'photo' => $user->detail->photo ?? null,
                'note' => $user->detail->note ?? null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Actualizar un usuario",
     *     description="Actualiza la información de un usuario existente",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jane Doe", description="Nombre del usuario"),
     *             @OA\Property(property="email", type="string", format="email", example="janedoe@example.com", description="Correo electrónico del usuario"),
     *             @OA\Property(property="password", type="string", format="password", example="NewPassword123!", description="Nueva contraseña del usuario (opcional)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario actualizado exitosamente."),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde."),
     *             @OA\Property(property="error", type="string")
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
     *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="El campo contraseña debe contener al menos una letra mayúscula."))
     *             )
     *         )
     *     )
     * )
     */
    public function update(UpdateUserRequest $request)
    {
        try {
            $user = User::findOrFail(Auth::id());

            $user->update($request->only(['name']));
            $user->detail()->update($request->only(['phone', 'nickname']));
    
            return response()->json([
                'message' => 'Actualizado exitosamente.',
                // 'name' => $user->name,
                // 'nickname' => $user->detail->nickname,
                // 'phone' => $user->detail->phone, //revisar si debe ser unico
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
   public function updatePhoto(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $imageName = Str::random(10) . '.jpg';
            $filePath = "images/perfiles/" . $imageName;
            $userDetail = UserDetail::where('user_id', Auth::id())->first();
            if ($userDetail && $userDetail->photo) {
                $oldImagePath = str_replace(asset('storage/'), '', $userDetail->photo);
                if (Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }
            Storage::disk('public')->put($filePath, file_get_contents($image));
            $imageUrl = asset('storage/' . $filePath);
            $userDetail->update(['photo' => $imageUrl]);
            return response()->json([
                'url' => $imageUrl
            ], 200);
            
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Eliminar un usuario",
     *     description="Elimina un usuario del sistema",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El usuario ha pasado a estar inactivo.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        //
        try {
            $user = User::findOrFail($id);
            
            $user->delete(); 
            //se debe es actualizar el estado del perfil
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
