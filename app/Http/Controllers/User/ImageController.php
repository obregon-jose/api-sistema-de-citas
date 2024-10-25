<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    //
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/images', $imageName);

            return response()->json(['url' => Storage::url($path)], 200);
            
        }

        return response()->json(['error' => 'No image uploaded'], 400);
        
        // Subir la imagen del usuario
        // $image = $request->file('image');
        // $imageName = time().'.'.$image->extension();
        // $image->move(public_path('images'), $imageName);

        // return response()->json([
        //     'message' => 'Imagen subida con éxito.',
        //     'image' => $imageName,
        // ]);
    }
}
