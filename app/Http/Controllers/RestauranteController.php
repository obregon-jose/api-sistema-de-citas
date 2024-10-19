<?php

namespace App\Http\Controllers;


use App\Models\Restaurante;
use Illuminate\Http\Request;

class RestauranteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return Restaurante::all();

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $restaurante = Restaurante::create($request->all());
        return response()->json($restaurante, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        return Restaurante::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $restaurante = Restaurante::findOrFail($id);
        $restaurante->update($request->all());
        return response()->json($restaurante, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy ($id)
    {
        //
        Restaurante::destroy($id);
        return response()->json(null, 204);
    }
}
