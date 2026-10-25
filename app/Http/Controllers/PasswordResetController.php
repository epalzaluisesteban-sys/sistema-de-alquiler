<?php

namespace App\Http\Controllers;

use App\Models\RecuperacionPassword;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    // El sistema no tiene columna de email ni mailer configurado (login es
    // por cédula), así que la recuperación verifica cédula + teléfono en vez
    // de enviar un enlace por correo.
    private const MINUTOS_VALIDEZ = 15;

    public function mostrarFormularioSolicitud()
    {
        return view('auth.olvide-password');
    }

    public function enviarSolicitud(Request $request)
    {
        $datos = $request->validate([
            'cedula' => ['required', 'string', 'max:20'],
            'telefono' => ['required', 'string', 'max:25'],
        ]);

        $usuario = Usuario::where('cedula', trim($datos['cedula']))
            ->where('telefono', trim($datos['telefono']))
            ->first();

        // Mensaje genérico: no revela si falló la cédula o el teléfono, para
        // no ayudar a adivinar datos de otra persona.
        if (! $usuario) {
            return back()->withErrors([
                'cedula' => 'La cédula y el teléfono no coinciden con ningún usuario registrado.',
            ])->withInput();
        }

        // Cualquier enlace anterior sin usar queda invalidado al pedir uno nuevo.
        RecuperacionPassword::where('usuario_id', $usuario->id)->delete();

        $token = Str::random(60);

        RecuperacionPassword::create([
            'usuario_id' => $usuario->id,
            'token' => Hash::make($token),
            'expira_en' => now()->addMinutes(self::MINUTOS_VALIDEZ),
        ]);

        return redirect()->route('password.reset.form', [
            'usuario' => $usuario->id,
            'token' => $token,
        ]);
    }

    public function mostrarFormularioReset(Usuario $usuario, string $token)
    {
        if (! $this->tokenValido($usuario, $token)) {
            return redirect()->route('password.request')->withErrors([
                'cedula' => 'El enlace de recuperación no es válido o ya expiró. Solicita uno nuevo.',
            ]);
        }

        return view('auth.resetear-password', ['usuario' => $usuario, 'token' => $token]);
    }

    public function resetear(Request $request, Usuario $usuario, string $token)
    {
        if (! $this->tokenValido($usuario, $token)) {
            return redirect()->route('password.request')->withErrors([
                'cedula' => 'El enlace de recuperación no es válido o ya expiró. Solicita uno nuevo.',
            ]);
        }

        $validado = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        // Vía el atributo virtual 'password' para que se hashee correctamente
        // (ver nota sobre Usuario::setPasswordAttribute en CLAUDE.md).
        $usuario->update(['password' => $validado['password']]);

        // Enlace de un solo uso: se consume apenas se usa con éxito.
        RecuperacionPassword::where('usuario_id', $usuario->id)->delete();

        return redirect()->route('login')->with('success', 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
    }

    private function tokenValido(Usuario $usuario, string $token): bool
    {
        $registro = RecuperacionPassword::where('usuario_id', $usuario->id)
            ->where('expira_en', '>', now())
            ->latest()
            ->first();

        return $registro && Hash::check($token, $registro->token);
    }
}
