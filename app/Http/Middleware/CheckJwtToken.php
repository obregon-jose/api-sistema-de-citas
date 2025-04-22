<?php

namespace App\Http\Middleware;

// use Closure;
// use Exception;
// use Illuminate\Http\Request;
// use Tymon\JWTAuth\Facades\JWTAuth;
// use Tymon\JWTAuth\Exceptions\TokenExpiredException;
// use Tymon\JWTAuth\Exceptions\TokenInvalidException;
// use Symfony\Component\HttpFoundation\Response;

// class CheckJwtToken
// {
//     public function handle(Request $request, Closure $next): Response
//     {
//         try {
//             // Intenta obtener el usuario desde el token
//             $user = JWTAuth::parseToken()->authenticate();
//         } catch (TokenExpiredException $e) {
//             return response()->json(['message' => 'Token expirado'], 401);
//         } catch (TokenInvalidException $e) {
//             return response()->json(['message' => 'Token inválido'], 401);
//         } catch (Exception $e) {
//             return response()->json(['message' => 'Token no encontrado o inválido'], 401);
//         }

//         // Si el token está bien, continúa
//         return $next($request);
//     }
// }


// use Closure;
// use Illuminate\Auth\AuthenticationException;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Symfony\Component\HttpFoundation\Response;

// class CheckJwtToken
// {
//     public function handle(Request $request, Closure $next): Response
//     {
//         if (!Auth::check()) {
//             return response()->json([
//                 'message' => 'No autenticado',
//             ], 401);
//         }

//         return $next($request);
//     }
// }


use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckJwtToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->bearerToken();

        if (!$header) {
            return response()->json(['message' => 'Token no proporcionado'], 401);
        }

        $token = PersonalAccessToken::findToken($header);

        if (!$token || !$token->tokenable) {
            return response()->json(['message' => 'Token inválido o expirado'], 401);
        }

        Auth::setUser($token->tokenable); // Autentica manualmente
        return $next($request);
    }
}
