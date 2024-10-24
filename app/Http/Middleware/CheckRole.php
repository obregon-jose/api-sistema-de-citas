<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;



class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = Auth::user();  // Obtiene el usuario autenticado

        // Verifica si el usuario tiene el rol en uno de sus perfiles
        $hasRole = $user->profiles()->whereHas('role', function ($query) use ($role) {
            $query->where('name', $role);  // Compara el nombre del rol
        })->exists();

        if (!$hasRole) {
            // Retorna error si el usuario no tiene el rol requerido
            return response()->json(['message' => 'Acceso denegado. No tienes los permisos necesarios.'], 403);
        }

        return $next($request);  // Permite continuar si el rol es correcto
    }
}
