<?php

namespace App\Http\Controllers;

use App\Events\ServiceUpdate;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/services",
     *     summary="Listar todos los servicios",
     *     description="Obtiene una lista de todos los servicios",
     *     tags={"Service"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de servicios obtenida con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="services", type="array", @OA\Items(type="object"))
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
        $services = Service::all();
        return response()->json([
            'services'=>$services,
        ],200);
    }

    /**
     * @OA\Post(
     *     path="/api/services",
     *     summary="Crear un nuevo servicio",
     *     description="Registra un nuevo servicio en el sistema",
     *     tags={"Service"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Corte de cabello", description="Nombre del servicio"),
     *             @OA\Property(property="price", type="integer", example=1500, description="Precio del servicio (opcional)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Servicio creado con éxito",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Servicio creado con éxito."),
     *             @OA\Property(property="service", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde."),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="El campo nombre es obligatorio.")),
     *                 @OA\Property(property="price", type="array", @OA\Items(type="string", example="El precio debe ser un número entero."))
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        //
        try {
            // Verificar si el servicio ya está registrado
            if (Service::where('name', $request->name)->exists()) {
                return response()->json([
                    'message' => 'Ya existe el servicio.',
                ], 400);
            }
            
            $service = Service::create($request->all());
            // Emitir el evento con los datos del servicio actualizado
            broadcast(new ServiceUpdate($service));
            // event(new ServiceUpdate($service));

            return response()->json([
                'message' => 'servicio creado con éxito.',
                // 'service' => $service,
            ], 201);
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $err->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/services/{id}",
     *     summary="Obtener un servicio por ID",
     *     description="Obtiene la información de un servicio específico",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Servicio encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="service", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servicio no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Servicio no encontrado."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        //
        try {
            $service = Service::findOrFail($id);
            return response()->json([
                'service' => $service,
            ], 200);
        } catch (\Exception $err) {
            return response()->json([
                'message' => $err->getMessage(),
                'error' => 'service no encontrado.',
            ], 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/services/{id}",
     *     summary="Actualizar un servicio",
     *     description="Actualiza la información de un servicio existente",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Corte de cabello", description="Nombre del servicio"),
     *             @OA\Property(property="price", type="integer", example=1500, description="Nuevo precio del servicio (opcional)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Servicio actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Servicio actualizado exitosamente."),
     *             @OA\Property(property="service", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servicio no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Servicio no encontrado."),
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
     *                 @OA\Property(property="price", type="array", @OA\Items(type="string", example="El precio debe ser un número entero."))
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        //
        try {
            $service = Service::findOrFail($id);
            $validatedService = $request->validate([
                'name' => 'nullable|unique:services,name,'.$service->id,
                'price' => 'nullable|integer',
            ]);

            $service->update($validatedService);
            broadcast(new ServiceUpdate($service));
            return response()->json([
                'message' => 'Servicio actualizado',
                // 'servicio' => $service,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/services/{id}",
     *     summary="Eliminar un servicio",
     *     description="Elimina un servicio del sistema",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Servicio eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="El servicio ha pasado a estar inactivo.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Servicio no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Servicio no encontrado."),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        //
        try {
            $service = service::findOrFail($id);
            $serviceDelete = $service;
            // broadcast(new ServiceUpdate($serviceDelete));
            // Emitir evento indicando eliminación
            // broadcast(new ServiceUpdate([
            //     'action' => 'delete',
            //     'serviceId' => $serviceDelete,
            // ]));
            $service->delete();

            return response()->json([
                'message' => 'El service ha pasado a estar inactivo.', //esto esta eliminando
            ], 200);
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $err->getMessage(),
            ], 500);
        }
    }
}
