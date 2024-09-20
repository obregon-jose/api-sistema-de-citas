<?php

namespace App\Http\Controllers;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $services=Service::all();
        return response()->json([
            'services'=>$services,
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try {
            $validatedData = $request->validate([
                "name" => "required|string|unique:services",
                "price" => "nullable|integer"
                
            ]);
            

            $service = Service::create($validatedData);

            return response()->json([
                'message' => 'servicio creado con éxito.',
                'service' => $service,
            ], 201);
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $err->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        try {
            $service = Service::findOrFail($id);
            $validatedService = $request->validate([
                'name' => 'nullable|string|max:100|unique:services,name,'.$service->id,
                'price' => 'nullable|integer',
                
            ]);


            
            $service->update($validatedService);

            return response()->json([
                'message' => 'Servicio actualizado exitosamente.',
                'servicio' => $service,
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
            $service = service::findOrFail($id);
            $service->delete();
            return response()->json([
                'message' => 'El service ha pasado a estar inactivo.',
            ], 200);
        } catch (\Exception $err) {
            return response()->json([
                'message' => $err->getMessage(),
                'error' => 'Error al inactivar el servicio.',
            ], 500);
        }
    }
}
