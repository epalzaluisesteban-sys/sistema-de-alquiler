<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Pago;
use App\Models\Reporte;
use App\Models\Usuario;
use App\Support\Personalizacion;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $inquilinosActivos = Asignacion::porPropietario()->where('estado_asignacion', 'activo')->distinct('usuario_id')->count('usuario_id');
        $pagosPendientes = Pago::porPropietario()->whereIn('estado_pago', ['pendiente', 'notificado'])->count();
        $ingresosMes = Pago::porPropietario()->where('estado_pago', 'confirmado')
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('monto');

        $ultimosMovimientos = Pago::porPropietario()
            ->with(['asignacion.usuario', 'asignacion.habitacion.propiedad', 'asignacion.propiedad'])
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $data = compact('inquilinosActivos', 'pagosPendientes', 'ingresosMes', 'ultimosMovimientos');

        return view('panel.dashboard', $data);
    }

    public function notificaciones()
    {
        $reportes = Reporte::porPropietario()
            ->whereIn('estado_reporte', ['pendiente', 'en_revision'])
            ->with(['asignacion.usuario', 'asignacion.habitacion.propiedad', 'asignacion.propiedad'])
            ->orderByDesc('fecha')
            ->get();

        $avisosPago = Pago::porPropietario()
            ->whereIn('estado_pago', ['pendiente', 'notificado'])
            ->with(['asignacion.usuario', 'asignacion.habitacion.propiedad', 'asignacion.propiedad'])
            ->orderByDesc('fecha')
            ->get();

        return view('panel.notificaciones', compact('reportes', 'avisosPago'));
    }

    public function ajustes()
    {
        $inquilinos = Usuario::where('rol', 'inquilino')
            ->porPropietario()
            ->withCount(['asignaciones' => fn ($query) => $query->porPropietario()])
            ->with(['asignaciones' => function ($query) {
                $query->porPropietario()
                    ->where('estado_asignacion', 'activo')
                    ->latest('fecha_inicio')
                    ->with(['habitacion.propiedad', 'propiedad']);
            }])
            ->orderBy('nombre')
            ->get();

        return view('panel.ajustes', [
            'usuario' => auth()->user(),
            'fondos' => Personalizacion::FONDOS,
            'colores' => Personalizacion::COLORES,
            'fuentes' => Personalizacion::FUENTES,
            'botones' => Personalizacion::BOTONES,
            'inquilinos' => $inquilinos,
        ]);
    }

    public function updateAjustes(Request $request)
    {
        $usuario = auth()->user();

        $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'apellido' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'cedula' => ['required', 'string', 'max:20', 'regex:/^[VE]-\d{6,9}$/', 'unique:usuarios,cedula,' . $usuario->id],
            'telefono' => ['nullable', 'string', 'max:25', 'regex:/^\+\d{1,4}\s\d{6,15}$/'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'nombre.regex' => 'El nombre solo puede contener letras.',
            'apellido.regex' => 'El apellido solo puede contener letras.',
            'cedula.regex' => 'La cédula debe tener el formato V-12345678 (venezolano) o E-12345678 (extranjero).',
            'telefono.regex' => 'El teléfono debe incluir el prefijo del país y solo números, ej. +58 4121234567.',
        ]);

        $data = [
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'cedula' => $request->cedula,
            'telefono' => $request->telefono,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $usuario->update($data);

        return redirect()->route('admin.ajustes')->with('success', 'Ajustes actualizados correctamente.');
    }

    public function updatePersonalizacion(Request $request)
    {
        $usuario = auth()->user();

        $data = $request->validate([
            'tema_fondo' => ['nullable', 'in:' . implode(',', array_keys(Personalizacion::FONDOS))],
            'tema_color' => ['nullable', 'in:' . implode(',', array_keys(Personalizacion::COLORES))],
            'tema_fuente' => ['nullable', 'in:' . implode(',', array_keys(Personalizacion::FUENTES))],
            'tema_boton' => ['nullable', 'in:' . implode(',', array_keys(Personalizacion::BOTONES))],
        ]);

        $usuario->update($data);

        return redirect()->route('admin.ajustes')->with('success', 'Personalización actualizada correctamente.');
    }

    public function updatePrecioHabitacion(Request $request)
    {
        $usuario = auth()->user();

        $data = $request->validate([
            'precio_habitacion' => ['required', 'numeric', 'min:0'],
        ]);

        $usuario->update($data);

        return redirect()->route('admin.ajustes')->with('success', 'Precio de habitación actualizado correctamente.');
    }
}
