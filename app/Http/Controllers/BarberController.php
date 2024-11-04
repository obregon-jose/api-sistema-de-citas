<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    //
    public function index()
    {
        // Obtener el ID del rol "peluquero"
        $barberRoleId = DB::table('roles')->where('name', 'peluquero')->value('id');

        // Consultar todos los usuarios con el rol de "peluquero" y sus detalles
        $barbers = User::whereHas('profiles', function ($query) use ($barberRoleId) {
                $query->where('role_id', $barberRoleId);
            })
            ->with(['profiles' => function ($query) use ($barberRoleId) {
                $query->where('role_id', $barberRoleId);
            }, 'detail']) // Cargar también los detalles del usuario
            ->get();

        return response()->json($barbers);
    }
}
