<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AsignaResidencia;
use App\Models\Asignacion;
use App\Models\Habitacion;
use App\Models\Inquilino;
use App\Models\Pago;
use App\Models\Propiedad;
use App\Models\Usuario;
use Illuminate\Http\Request;

class InquilinoController extends Controller
{
    use AsignaResidencia;

    public function inquilinos()
    {
        $inquilinos = Usuario::where('rol', 'inquilino')
            ->porPropietario()
            ->with(['asignaciones' => function ($query) {
                $query->porPropietario()
                    ->where('estado_asignacion', 'activo')
                    ->latest('fecha_inicio')
                    ->with(['habitacion.propiedad', 'propiedad']);
            }])
            ->orderBy('nombre')
            ->get();

        return view('panel.inquilinos', compact('inquilinos'));
    }

    public function nuevoInquilino(Request $request)
    {
        // Solo propiedades sin habitaciones se pueden alquilar como "Casa"
        // completa; las que tienen habitaciones se alquilan por habitación.
        $properties = Propiedad::porPropietario()->where('estado', 'disponible')->doesntHave('habitaciones')->orderBy('nombre')->get();
        $rooms = Habitacion::porPropietario()->where('estado', 'disponible')->with('propiedad.usuario')->orderBy('numero')->get();

        // Preselección al llegar desde la tabla de habitaciones (click en
        // una habitación "disponible"). Si ya no está disponible, se
        // ignora silenciosamente.
        $habitacionPreseleccionada = $request->filled('habitacion_id')
            ? $rooms->firstWhere('id', (int) $request->habitacion_id)
            : null;

        // Preselección al llegar desde la tabla "Casa Entera" de propiedades
        // (click en una propiedad "disponible"). Si ya no está disponible,
        // se ignora silenciosamente.
        $propiedadPreseleccionada = $request->filled('propiedad_id')
            ? $properties->firstWhere('id', (int) $request->propiedad_id)
            : null;

        return view('panel.nuevo-inquilino', compact('properties', 'rooms', 'habitacionPreseleccionada', 'propiedadPreseleccionada'));
    }

    public function storeInquilino(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'apellido' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'cedula' => ['required', 'string', 'max:20', 'regex:/^[VE]-\d{6,9}$/', 'unique:usuarios,cedula'],
            'password' => ['required', 'string', 'min:6'],
            'telefono' => ['nullable', 'string', 'max:25', 'regex:/^\+\d{1,4}\s\d{6,15}$/'],
            'rental_type' => ['required', 'in:Casa,Habitación'],
            'property_id' => ['required', 'integer'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ], [
            'nombre.regex' => 'El nombre solo puede contener letras.',
            'apellido.regex' => 'El apellido solo puede contener letras.',
            'cedula.regex' => 'La cédula debe tener el formato V-12345678 (venezolano) o E-12345678 (extranjero).',
            'telefono.regex' => 'El teléfono debe incluir el prefijo del país y solo números, ej. +58 4121234567.',
        ]);

        // El precio no se pide en este formulario: para "Casa" se toma el
        // precio acordado de la propiedad, para "Habitación" el precio
        // definido globalmente en Ajustes (ya guardado en la habitación).
        $residencia = $request->rental_type === 'Casa'
            ? Propiedad::porPropietario()->find($request->property_id)
            : Habitacion::porPropietario()->find($request->property_id);

        if (! $residencia) {
            return back()->withInput()->withErrors([
                'property_id' => 'La residencia seleccionada no es válida.',
            ]);
        }

        $precio = $request->rental_type === 'Casa'
            ? $residencia->precio_acordado
            : $residencia->precio_actual;

        if ($precio === null) {
            return back()->withInput()->withErrors([
                'property_id' => $request->rental_type === 'Casa'
                    ? 'Esta propiedad no tiene un precio acordado. Configúralo en Editar Propiedad.'
                    : 'Esta habitación no tiene precio. Configúralo en Ajustes › Precio de Habitación.',
            ]);
        }

        $user = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'cedula' => $request->cedula,
            'password' => $request->password,
            'role' => 'inquilino',
            'telefono' => $request->telefono,
        ]);

        Inquilino::create(['usuario_id' => $user->id]);

        $asignacion = $this->asignarResidencia(
            $user,
            $request->rental_type,
            (int) $request->property_id,
            (float) $precio,
            $request->fecha_inicio,
            $request->fecha_fin
        );

        if ($asignacion) {
            Pago::create([
                'asignacion_id' => $asignacion->id,
                'monto' => $precio,
                'fecha' => $request->fecha_inicio,
                'estado_pago' => 'pendiente',
            ]);
        }

        return redirect()->route('admin.inquilinos')->with('success', 'Inquilino registrado con éxito.');
    }

    public function editInquilino($id)
    {
        $inquilino = Usuario::where('rol', 'inquilino')->porPropietario()->findOrFail($id);

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

        return view('panel.editar-inquilino', compact('inquilino', 'asignacionActiva', 'properties', 'rooms'));
    }

    public function updateInquilino(Request $request, $id)
    {
        $inquilino = Usuario::where('rol', 'inquilino')->porPropietario()->findOrFail($id);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'apellido' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'cedula' => ['required', 'string', 'max:20', 'regex:/^[VE]-\d{6,9}$/', 'unique:usuarios,cedula,' . $id],
            'password' => ['nullable', 'string', 'min:6'],
            'telefono' => ['nullable', 'string', 'max:25', 'regex:/^\+\d{1,4}\s\d{6,15}$/'],
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

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $inquilino->update($data);

        $tipo = $request->input('rental_type');
        $propertyId = $request->input('property_id');

        if ($tipo && $propertyId) {
            $asignacionActiva = Asignacion::porPropietario()
                ->where('usuario_id', $id)
                ->where('estado_asignacion', 'activo')
                ->latest('fecha_inicio')
                ->first();

            $yaAsignadoAhi = $asignacionActiva && (
                ($tipo === 'Casa' && (int) $asignacionActiva->propiedad_id === (int) $propertyId) ||
                ($tipo === 'Habitación' && (int) $asignacionActiva->habitacion_id === (int) $propertyId)
            );

            if (! $yaAsignadoAhi) {
                if ($asignacionActiva) {
                    $this->liberarAsignacion($asignacionActiva);
                }

                $this->asignarResidencia($inquilino, $tipo, (int) $propertyId);
            }
        }

        return redirect()->route('admin.inquilinos')->with('success', 'Datos del inquilino actualizados.');
    }

    /**
     * Historial de un inquilino: todas sus asignaciones (residencias
     * pasadas y activa) junto con los pagos de cada una.
     */
    public function historial($id)
    {
        // Accesible también para encargados que viven en una propiedad: el
        // historial de residencias/pagos no depende del rol, solo de tener
        // asignaciones.
        $inquilino = Usuario::whereIn('rol', ['inquilino', 'encargado'])->porPropietario()->findOrFail($id);

        // Solo las residencias de este propietario: si el inquilino también
        // alquiló con otro propietario, ese historial no se expone aquí.
        $asignaciones = Asignacion::porPropietario()
            ->where('usuario_id', $id)
            ->with(['habitacion.propiedad', 'propiedad', 'pagos'])
            ->orderByDesc('fecha_inicio')
            ->get();

        return view('panel.historial-inquilino', compact('inquilino', 'asignaciones'));
    }

    /**
     * Desaloja al inquilino: finaliza su(s) asignación(es) activa(s) y
     * libera la habitación/propiedad, pero conserva su cuenta y su
     * historial de residencias y pagos (no se borra al usuario).
     */
    public function desalojarInquilino($id)
    {
        Usuario::where('rol', 'inquilino')->porPropietario()->findOrFail($id);

        Asignacion::porPropietario()
            ->where('usuario_id', $id)
            ->where('estado_asignacion', 'activo')
            ->get()
            ->each(fn (Asignacion $asignacion) => $this->liberarAsignacion($asignacion));

        return redirect()->route('admin.inquilinos')->with('success', 'Inquilino desalojado correctamente. Su historial se conserva.');
    }
}
