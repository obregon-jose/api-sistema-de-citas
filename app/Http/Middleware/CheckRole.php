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
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        $userRoles = $user->profiles()->with('role')->get()->pluck('role.name')->unique()->toArray();

        $hasRole = !empty(array_intersect($roles, $userRoles));

        if (!$hasRole) {
            // Retorna error si el usuario no tiene ninguno de los roles requeridos
            return response()->json([
                'message' => 'Acceso denegado. No tienes los permisos necesarios.',
            ], 403);
        }
        return $next($request);
    }
}
