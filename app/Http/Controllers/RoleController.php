<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    //
    public function index()
    {
        // $roles = Role::all();
        // return response()->json($roles, 200);

        $user = Auth::user();
        $userRole = $user->profiles()->with('role')->get()->pluck('role.name')->unique()->toArray();

        $roleMapping = [
            'root' => ['cliente', 'peluquero', 'administrador', 'dueño'],
            'dueño' => ['cliente', 'peluquero', 'administrador'],
            'administrador' => ['cliente', 'peluquero'],
            'peluquero' => ['cliente']
        ];
        
        $roles = collect();
        
        foreach ($roleMapping as $role => $allowedRoles) {
            if (in_array($role, $userRole)) {
                $roles = Role::whereIn('name', $allowedRoles)->get();
                break;
            }
        }
        
        return response()->json([
            'roles' => $roles,
        ], 200);

    }
}
