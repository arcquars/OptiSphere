<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNoPanelAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $panel = Filament::getCurrentPanel();

        // 1. Si no hay usuario o no hay panel, seguimos el flujo normal
        if (!$user || !$panel) {
            return $next($request);
        }

        /** @var \App\Models\User $user */
        // 2. Utilizamos la lógica que ya tienes en el modelo User.php
        if (!$user->canAccessPanel($panel)) {
            
            // 3. Forzamos el logout para limpiar la sesión corrupta
            Auth::guard('web')->logout();
            
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // 4. Redirigimos al login con un mensaje de error
            return redirect()->route('filament.admin.auth.login') // Ajusta según tu ruta de login principal
                ->with('error', 'No tienes permisos para acceder a este panel.');
        }

        return $next($request);
    }
}
