<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            //Envoie l'objet auth et tout le reste en html, avec un success
            $auth = [
                'user' => auth()->user(),
                'is_admin' => auth()->check() && auth()->user()->role === 'admin',
            ];
            // On peut utiliser la méthode success pour renvoyer une réponse JSON
            return response()->json([
                'message' => 'Unauthorized',
                'auth' => $auth,
                'role' => auth()->user() ? auth()->user()->role : 'guest',
            ], Response::HTTP_FORBIDDEN); // 403 Forbidden
            // Ou si tu veux renvoyer un 404 Not Found
            success(404, auth);
    }

    return $next($request);
    }

}
