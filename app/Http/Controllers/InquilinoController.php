<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EsEncargado;
use App\Models\Asignacion;
use App\Models\Habitacion;
use App\Models\Propiedad;
use App\Models\Usuario;
use Illuminate\Http\Request;

class InquilinoController extends Controller
{
    use EsEncargado;

    /**
     * Crea la Asignacion que liga a un inquilino con una Propiedad completa ("Casa")
     * o con una Habitacion puntual, y marca esa residencia como ocupada.
     */
    private function asignarResidencia(Usuario $usuario, string $tipo, int $id): void
    {
        if ($tipo === 'Casa') {
            $propiedad = Propiedad::find($id);

            if (! $propiedad) {
                return;
            }

            Asignacion::create([
                'usuario_id' => $usuario->id,
                'propiedad_id' => $propiedad->id,
                'fecha_inicio' => now()->toDateString(),
                'estado_asignacion' => 'activo',
            ]);

            $propiedad->update(['estado' => 'ocupada']);

            return;
        }

        $habitacion = Habitacion::find($id);

        if (! $habitacion) {
            return;
        }

        Asignacion::create([
            'usuario_id' => $usuario->id,
            'habitacion_id' => $habitacion->id,
            'fecha_inicio' => now()->toDateString(),
            'estado_asignacion' => 'activo',
        ]);

        $habitacion->update(['estado' => 'ocupada']);
    }

    /**
     * Cierra una Asignacion activa y libera (vuelve a "disponible") la
     * Habitacion o Propiedad que tenía ocupada.
     */
    private function liberarAsignacion(Asignacion $asignacion): void
    {
        $asignacion->habitacion?->update(['estado' => 'disponible']);
        $asignacion->propiedad?->update(['estado' => 'disponible']);

        $asignacion->update([
            'estado_asignacion' => 'finalizada',
            'fecha_fin' => now()->toDateString(),
        ]);
    }

    public function inquilinos()
    {
        $inquilinos = Usuario::where('rol', 'inquilino')
            ->with(['asignaciones' => function ($query) {
                $query->where('estado_asignacion', 'activo')
                    ->latest('fecha_inicio')
                    ->with(['habitacion.propiedad', 'propiedad']);
            }])
            ->orderBy('nombre')
            ->get();

        return view($this->esEncargado() ? 'Encargado.inquilinos' : 'Propietario.inquilinos', compact('inquilinos'));
    }

    public function nuevoInquilino()
    {
        $properties = Propiedad::where('estado', 'disponible')->orderBy('nombre')->get();
        $rooms = Habitacion::where('estado', 'disponible')->with('propiedad')->orderBy('numero')->get();

        return view($this->esEncargado() ? 'Encargado.nuevo-inquilino' : 'Propietario.nuevo-inquilino', compact('properties', 'rooms'));
    }

    public function storeInquilino(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'cedula' => ['required', 'string', 'max:20', 'unique:usuarios,cedula'],
            'password' => ['required', 'string', 'min:6'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'rental_type' => ['required', 'in:Casa,Habitación'],
            'property_id' => ['required', 'integer'],
        ]);

        $user = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'cedula' => $request->cedula,
            'password' => $request->password,
            'role' => 'inquilino',
            'telefono' => $request->telefono,
        ]);

        $this->asignarResidencia($user, $request->rental_type, (int) $request->property_id);

        return redirect()->route('admin.inquilinos')->with('success', 'Inquilino registrado con éxito.');
    }

    public function editInquilino($id)
    {
        $inquilino = Usuario::findOrFail($id);

        $asignacionActiva = Asignacion::where('usuario_id', $id)
            ->where('estado_asignacion', 'activo')
            ->latest('fecha_inicio')
            ->first();

        $properties = Propiedad::where('estado', 'disponible')
            ->orWhere('id', $asignacionActiva?->propiedad_id)
            ->orderBy('nombre')
            ->get();

        $rooms = Habitacion::where('estado', 'disponible')
            ->orWhere('id', $asignacionActiva?->habitacion_id)
            ->with('propiedad')
            ->orderBy('numero')
            ->get();

        return view($this->esEncargado() ? 'Encargado.editar-inquilino' : 'Propietario.editar-inquilino', compact('inquilino', 'asignacionActiva', 'properties', 'rooms'));
    }

    public function updateInquilino(Request $request, $id)
    {
        $inquilino = Usuario::findOrFail($id);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'cedula' => ['required', 'string', 'max:20', 'unique:usuarios,cedula,' . $id],
            'password' => ['nullable', 'string', 'min:6'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'rental_type' => ['nullable', 'in:Casa,Habitación'],
            'property_id' => ['nullable', 'integer'],
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
            $asignacionActiva = Asignacion::where('usuario_id', $id)
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

    public function destroyInquilino($id)
    {
        $user = Usuario::findOrFail($id);

        Asignacion::where('usuario_id', $id)
            ->where('estado_asignacion', 'activo')
            ->get()
            ->each(fn (Asignacion $asignacion) => $this->liberarAsignacion($asignacion));

        $user->delete();

        return redirect()->route('admin.inquilinos')->with('success', 'Inquilino eliminado correctamente.');
    }
}
