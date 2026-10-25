<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AsignaResidencia;
use App\Models\Asignacion;
use App\Models\Habitacion;
use App\Models\Inquilino;
use App\Models\Propiedad;
use App\Models\Usuario;
use Illuminate\Http\Request;

class EncargadoController extends Controller
{
    use AsignaResidencia;

    public function encargados()
    {
        $encargados = auth()->user()->encargados()
            ->with(['asignaciones' => function ($query) {
                $query->porPropietario()
                    ->where('estado_asignacion', 'activo')
                    ->latest('fecha_inicio')
                    ->with(['habitacion.propiedad', 'propiedad']);
            }])
            ->orderBy('nombre')
            ->get();

        return view('Propietario.encargados', compact('encargados'));
    }

    public function nuevoEncargado()
    {
        // Mismo criterio que "Nuevo Inquilino": solo propiedades sin
        // habitaciones se pueden asignar como "Casa" completa; las que
        // tienen habitaciones se asignan por habitación.
        $properties = Propiedad::porPropietario()->where('estado', 'disponible')->doesntHave('habitaciones')->orderBy('nombre')->get();
        $rooms = Habitacion::porPropietario()->where('estado', 'disponible')->with('propiedad.usuario')->orderBy('numero')->get();

        return view('Propietario.nuevo-encargado', compact('properties', 'rooms'));
    }

    public function storeEncargado(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'apellido' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'cedula' => ['required', 'string', 'max:20', 'regex:/^[VE]-\d{6,9}$/', 'unique:usuarios,cedula'],
            'telefono' => ['nullable', 'string', 'max:25', 'regex:/^\+\d{1,4}\s\d{6,15}$/'],
            'contrasena' => ['required', 'string', 'min:6'],
            'rental_type' => ['nullable', 'in:Casa,Habitación'],
            'property_id' => ['required_with:rental_type', 'nullable', 'integer'],
        ], [
            'nombre.regex' => 'El nombre solo puede contener letras.',
            'apellido.regex' => 'El apellido solo puede contener letras.',
            'cedula.regex' => 'La cédula debe tener el formato V-12345678 (venezolano) o E-12345678 (extranjero).',
            'telefono.regex' => 'El teléfono debe incluir el prefijo del país y solo números, ej. +58 4121234567.',
        ]);

        $encargado = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'cedula' => $request->cedula,
            'telefono' => $request->telefono,
            'password' => $request->contrasena,
            'role' => 'encargado',
            'propietario_id' => auth()->user()->propietarioId(),
        ]);

        if ($request->filled('rental_type') && $request->filled('property_id')) {
            $this->asignarResidencia($encargado, $request->rental_type, (int) $request->property_id);
        }

        return redirect()->route('admin.encargados')->with('success', 'Encargado registrado con éxito.');
    }

    public function editEncargado($id)
    {
        $encargado = auth()->user()->encargados()->findOrFail($id);

        $asignacionActiva = Asignacion::porPropietario()
            ->where('usuario_id', $id)
            ->where('estado_asignacion', 'activo')
            ->latest('fecha_inicio')
            ->first();

        $properties = Propiedad::porPropietario()
            ->where(fn ($q) => $q->where('estado', 'disponible')->orWhere('id', $asignacionActiva?->propiedad_id))
            ->orderBy('nombre')
            ->get();

        $rooms = Habitacion::porPropietario()
            ->where(fn ($q) => $q->where('estado', 'disponible')->orWhere('id', $asignacionActiva?->habitacion_id))
            ->with('propiedad')
            ->orderBy('numero')
            ->get();

        return view('Propietario.editar-encargado', compact('encargado', 'asignacionActiva', 'properties', 'rooms'));
    }

    public function updateEncargado(Request $request, $id)
    {
        $encargado = auth()->user()->encargados()->findOrFail($id);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'apellido' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'cedula' => ['required', 'string', 'max:20', 'regex:/^[VE]-\d{6,9}$/', 'unique:usuarios,cedula,' . $id],
            'telefono' => ['nullable', 'string', 'max:25', 'regex:/^\+\d{1,4}\s\d{6,15}$/'],
            'contrasena' => ['nullable', 'string', 'min:6'],
            'rental_type' => ['nullable', 'in:Casa,Habitación'],
            'property_id' => ['nullable', 'integer'],
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

        if ($request->filled('contrasena')) {
            $data['password'] = $request->contrasena;
        }

        $encargado->update($data);

        $tipo = $request->input('rental_type');
        $propertyId = $request->input('property_id');

        $asignacionActiva = Asignacion::porPropietario()
            ->where('usuario_id', $id)
            ->where('estado_asignacion', 'activo')
            ->latest('fecha_inicio')
            ->first();

        if ($tipo && $propertyId) {
            $yaAsignadoAhi = $asignacionActiva && (
                ($tipo === 'Casa' && (int) $asignacionActiva->propiedad_id === (int) $propertyId) ||
                ($tipo === 'Habitación' && (int) $asignacionActiva->habitacion_id === (int) $propertyId)
            );

            if (! $yaAsignadoAhi) {
                if ($asignacionActiva) {
                    $this->liberarAsignacion($asignacionActiva);
                }

                $this->asignarResidencia($encargado, $tipo, (int) $propertyId);
            }
        } elseif ($asignacionActiva) {
            // Se quitó la residencia: se libera la habitación/propiedad.
            $this->liberarAsignacion($asignacionActiva);
        }

        return redirect()->route('admin.encargados')->with('success', 'Encargado actualizado correctamente.');
    }

    /**
     * Delega a un inquilino ya registrado como encargado: cambia su rol sin
     * tocar su residencia actual (un encargado también puede vivir en una
     * propiedad, igual que se permite en storeEncargado/updateEncargado).
     */
    public function delegarInquilino($id)
    {
        $inquilino = Usuario::where('rol', 'inquilino')->porPropietario()->findOrFail($id);

        $inquilino->update([
            'role' => 'encargado',
            'propietario_id' => auth()->user()->propietarioId(),
        ]);

        Inquilino::where('usuario_id', $id)->delete();

        return redirect()->route('admin.encargados')->with('success', 'Inquilino delegado como encargado con éxito.');
    }

    /**
     * Revierte a un encargado a inquilino normal: cambia su rol sin tocar
     * su residencia actual (si vivía en una propiedad como encargado, sigue
     * viviendo ahí como inquilino).
     */
    public function revertirInquilino($id)
    {
        $encargado = auth()->user()->encargados()->findOrFail($id);

        $encargado->update(['role' => 'inquilino']);

        Inquilino::firstOrCreate(['usuario_id' => $id]);

        return redirect()->route('admin.encargados')->with('success', 'El encargado ahora es un inquilino normal.');
    }

    /**
     * Desaloja al encargado: finaliza su(s) asignación(es) activa(s) y
     * libera la habitación/propiedad, pero conserva su cuenta y su
     * historial de residencias y pagos (no se borra al usuario).
     */
    public function desalojarEncargado($id)
    {
        $encargado = auth()->user()->encargados()->findOrFail($id);

        Asignacion::porPropietario()
            ->where('usuario_id', $id)
            ->where('estado_asignacion', 'activo')
            ->get()
            ->each(fn (Asignacion $asignacion) => $this->liberarAsignacion($asignacion));

        return redirect()->route('admin.encargados')->with('success', 'Encargado desalojado correctamente. Su historial se conserva.');
    }
}
