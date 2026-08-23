<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Pago;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    private function asignacionActiva()
    {
        return Asignacion::where('usuario_id', Auth::user()->id)
            ->where('estado_asignacion', 'activo')
            ->with(['habitacion.propiedad', 'propiedad'])
            ->latest('fecha_inicio')
            ->first();
    }

    public function index()
    {
        $usuario = Auth::user();
        $asignacion = $this->asignacionActiva();

        return view('Inquilino.dashboard', compact('usuario', 'asignacion'));
    }

    public function residencias()
    {
        $usuario = Auth::user();
        $asignacion = $this->asignacionActiva();

        return view('Inquilino.residencias', compact('usuario', 'asignacion'));
    }

    public function pagos()
    {
        $pagos = Pago::whereHas('asignacion', function ($query) {
                $query->where('usuario_id', Auth::user()->id);
            })
            ->with(['asignacion.habitacion.propiedad', 'asignacion.propiedad'])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(10);

        return view('Inquilino.pagos', compact('pagos'));
    }

    public function notificarPago()
    {
        $asignacion = $this->asignacionActiva();

        return view('Inquilino.notificar-pago', compact('asignacion'));
    }

    public function storeNotificarPago(Request $request)
    {
        $asignacion = $this->asignacionActiva();

        if (! $asignacion) {
            return redirect()->route('tenant.dashboard')->with('error', 'No tienes un alquiler activo asignado.');
        }

        $validated = $request->validate([
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha' => ['required', 'date'],
            'comprobante' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $rutaComprobante = null;

        if ($request->hasFile('comprobante')) {
            $rutaComprobante = $request->file('comprobante')->store('comprobantes', 'public');
        }

        Pago::create([
            'asignacion_id' => $asignacion->id,
            'monto' => $validated['monto'],
            'fecha' => $validated['fecha'],
            'estado_pago' => 'notificado',
            'ruta_comprobante' => $rutaComprobante,
        ]);

        return redirect()->route('tenant.pagos')->with('success', 'Pago notificado correctamente. El encargado revisará y confirmará tu pago.');
    }

    public function reportes()
    {
        $reportes = Reporte::whereHas('asignacion', function ($query) {
                $query->where('usuario_id', Auth::user()->id);
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        $asignacion = $this->asignacionActiva();

        return view('Inquilino.reportes', compact('reportes', 'asignacion'));
    }

    public function storeReporte(Request $request)
    {
        $asignacion = $this->asignacionActiva();

        if (! $asignacion) {
            return redirect()->route('tenant.dashboard')->with('error', 'No tienes un alquiler activo asignado.');
        }

        $validated = $request->validate([
            'descripcion' => ['required', 'string', 'max:1000'],
        ]);

        Reporte::create([
            'asignacion_id' => $asignacion->id,
            'descripcion' => $validated['descripcion'],
            'fecha' => now()->toDateString(),
            'estado_reporte' => 'pendiente',
        ]);

        return redirect()->route('tenant.reportes')->with('success', 'Reporte enviado correctamente.');
    }

    public function ajustes()
    {
        return view('Inquilino.ajustes', ['usuario' => Auth::user()]);
    }

    public function updateAjustes(Request $request)
    {
        $usuario = Auth::user();

        $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'apellido' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'telefono' => ['nullable', 'string', 'max:25', 'regex:/^\+\d{1,4}\s\d{6,15}$/'],
            'password_actual' => ['required_with:password', 'string'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'nombre.regex' => 'El nombre solo puede contener letras.',
            'apellido.regex' => 'El apellido solo puede contener letras.',
            'telefono.regex' => 'El teléfono debe incluir el prefijo del país y solo números, ej. +58 4121234567.',
            'password_actual.required_with' => 'Debes ingresar tu contraseña actual para establecer una nueva.',
        ]);

        if ($request->filled('password') && ! Hash::check($request->password_actual, $usuario->contrasena)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.'])->withInput();
        }

        $data = [
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'telefono' => $request->telefono,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $usuario->update($data);

        return redirect()->route('tenant.ajustes')->with('success', 'Ajustes actualizados correctamente.');
    }
}
