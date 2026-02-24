<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockApiUsersFromWeb
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario está autenticado y tiene el rol 'user-api'
        if (Auth::check() && Auth::user()->hasRole('user-api')) {
            // Cerramos la sesión web inmediatamente
            Auth::logout();
            
            // Redirigimos con un mensaje de error o a una página 403
            return redirect()->route('login')->with('error', 'Su cuenta solo tiene acceso a la API.');
        }

        return $next($request);
    }
}
