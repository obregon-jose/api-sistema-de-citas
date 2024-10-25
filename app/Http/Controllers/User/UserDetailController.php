<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;

class UserDetailController extends Controller
{

    public function index()
    { 
        
    }

    public function store(Request $request)
    {

    }

    public function show($user_id)
    {

    }

    public function update(Request $request, $user_id)
    {
        try {
            $userDetail = UserDetail::where('user_id', $user_id)->firstOrFail();

            $validatedDetail = $request->validate([
                // resisar necesidad de validaciones
                'name' => 'nullable|string|max:255',
                'nickname' => 'nullable|string|max:255',
                'phone' => 'nullable|string|min:8|max:10',
                'photo' => 'nullable|string|max:255',
                'note' => 'nullable|string',
            ]);

            $userDetail->update($validatedDetail); // Actualiza el detalle de usuario

            // Actualiza el nombre en la tabla User
            User::where('id', $user_id)->update(['name' => $validatedDetail['name']]);
            $user = User::find($user_id);

            return response()->json([
                'message' => 'Perfil actualizado con éxito.',
                // 'user' => $user,
                // 'userDetail' => $userDetail,
            ], 200); 
        } catch (\Exception $err) {
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado. Por favor, inténtalo nuevamente más tarde.',
                'error' => $err->getMessage(),
            ], 400); 
        }
    }

    public function uploadImage(Request $request, $user_id)
    {
        $imageData = $request->input('image');

        $imageDecoded = base64_decode($imageData);

        $imageName = Str::random(10) . '.png';
        $filePath = "images/perfiles/{$user_id}/" . $imageName;

        $userDetail = UserDetail::where('user_id', $user_id)->first();
        if ($userDetail && $userDetail->photo) {
            $oldImagePath = str_replace(asset('storage/'), '', $userDetail->photo);
            if (Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }

        Storage::disk('public')->put($filePath, $imageDecoded);

        $imageUrl = asset('storage/' . $filePath);

        $userDetail->update(['photo' => $imageUrl]);

        return response()->json([
            'message' => 'Imagen subida con éxito.',
            // 'url' => $imageUrl
        ], 201);
    }

    public function destroy($user_id)
    {

    }
}
