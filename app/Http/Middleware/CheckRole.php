<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Si el usuario no está logueado, lo mandamos al inicio
        if (!Auth::check()) {
            return redirect('/');
        }

        // Si el rol del usuario en la base de datos NO coincide con ninguno de los permitidos, lo bloqueamos
        $usuario = Auth::user();
        $userRole = trim(strtolower($usuario->role ?? ''));
        $allowedRoles = array_map('strtolower', $roles);

        if (!in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Acceso Denegado. Esta área no corresponde a tu tipo de cuenta.');
        }

        return $next($request);
    }
}