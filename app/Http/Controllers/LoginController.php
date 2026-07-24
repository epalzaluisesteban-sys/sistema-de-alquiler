<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'cedula' => ['required', 'string', 'max:20'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credenciales)) {
            $request->session()->regenerate();

            // Obtenemos el usuario recién logueado
            $user = Auth::user();

            // Normalizamos el rol
            $role = trim(strtolower($user->role ?? ''));

            if (in_array($role, ['admin', 'encargado'])) {
                return redirect()->route('admin.dashboard')->with('success', 'Inicio de sesión exitoso.');
            } elseif ($role === 'tenant') {
                return redirect()->route('tenant.dashboard')->with('success', 'Inicio de sesión exitoso.');
            }

            // Si el rol no es ninguno de los anteriores, cerramos sesión por seguridad
            Auth::logout();
            return back()->withErrors(['cedula' => 'Usuario autenticado, pero sin rol válido asignado.']);
        }

        return back()->withErrors([
            'cedula' => 'La cédula o la contraseña son incorrectos.',
        ]);
    }

public function logout(Request $request)
    {
        // 1. Cierra la sesión en el sistema de autenticación
        Auth::logout();

        // 2. Invalida la sesión del usuario (limpia los datos del servidor)
        $request->session()->invalidate();

        // 3. Regenera el token CSRF (seguridad vital en Laravel)
        $request->session()->regenerateToken();

        // 4. Redirige a la página principal de bienvenida
        return redirect('/');
    }

}