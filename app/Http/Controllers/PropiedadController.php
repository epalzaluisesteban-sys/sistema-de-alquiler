<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Propiedad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropiedadController extends Controller
{
    public function propiedades()
    {
        $propiedades = Propiedad::withCount([
                'habitaciones',
                'habitaciones as habitaciones_ocupadas_count' => fn ($q) => $q->where('estado', 'ocupada'),
            ])
            ->with(['asignaciones' => fn ($q) => $q->where('estado_asignacion', 'activo')])
            ->orderBy('nombre')
            ->get();

        // Se muestran en dos grupos separados: las que se alquilan completas
        // (con precio acordado propio) y las que se subdividen en
        // habitaciones (su precio se maneja por habitación).
        $propiedadesCompletas = $propiedades->where('tipo', 'completa');
        $propiedadesConHabitaciones = $propiedades->where('tipo', 'habitaciones');

        return view('Propietario.propiedades', compact('propiedadesCompletas', 'propiedadesConHabitaciones'));
    }

    public function nuevaPropiedad()
    {
        return view('Propietario.nueva-propiedad');
    }

    public function storePropiedad(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['required', 'in:disponible,ocupada,mantenimiento'],
            'tipo' => ['required', 'in:completa,habitaciones'],
            'precio_acordado' => ['nullable', 'numeric', 'min:0'],
            'cantidad_habitaciones' => ['required_if:tipo,habitaciones', 'nullable', 'integer', 'min:1'],
        ]);

        $esHabitaciones = $validated['tipo'] === 'habitaciones';

        $propiedad = Propiedad::create([
            'usuario_id' => Auth::user()->id,
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'estado' => $validated['estado'],
            'tipo' => $validated['tipo'],
            'precio_acordado' => $esHabitaciones ? null : ($validated['precio_acordado'] ?? null),
            'cantidad_habitaciones' => $esHabitaciones ? (int) $validated['cantidad_habitaciones'] : null,
        ]);

        if ($esHabitaciones) {
            $this->crearHabitaciones($propiedad, (int) $validated['cantidad_habitaciones']);
        }

        return redirect()->route('admin.propiedades')->with('success', 'Propiedad registrada correctamente.');
    }

    public function editPropiedad($id)
    {
        $propiedad = Propiedad::findOrFail($id);

        return view('Propietario.editar-propiedad', compact('propiedad'));
    }

    public function updatePropiedad(Request $request, $id)
    {
        $propiedad = Propiedad::findOrFail($id);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['required', 'in:disponible,ocupada,mantenimiento'],
            'precio_acordado' => ['nullable', 'numeric', 'min:0'],
            'tipo' => [
                'required', 'in:completa,habitaciones',
                function ($attribute, $value, $fail) use ($propiedad) {
                    if ($propiedad->tipo === 'habitaciones' && $value === 'completa' && $propiedad->cantidad_habitaciones > 0) {
                        $fail('No se puede cambiar a "Casa completa" una propiedad que ya tiene habitaciones registradas.');
                    }
                },
            ],
            'cantidad_habitaciones' => [
                'required_if:tipo,habitaciones', 'nullable', 'integer', 'min:1',
                function ($attribute, $value, $fail) use ($propiedad) {
                    if ($propiedad->tipo === 'habitaciones' && $value !== null
                        && (int) $value <= (int) $propiedad->cantidad_habitaciones) {
                        $fail('La cantidad de habitaciones no puede ser menor o igual a la actual ('
                            . $propiedad->cantidad_habitaciones . '). Si necesitas reducirla, hazlo directamente en la base de datos.');
                    }
                },
            ],
        ]);

        $esHabitaciones = $validated['tipo'] === 'habitaciones';
        $cantidadAnterior = (int) ($propiedad->cantidad_habitaciones ?? 0);

        $propiedad->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'estado' => $validated['estado'],
            'tipo' => $validated['tipo'],
            'precio_acordado' => $esHabitaciones ? null : ($validated['precio_acordado'] ?? null),
            'cantidad_habitaciones' => $esHabitaciones ? (int) $validated['cantidad_habitaciones'] : null,
        ]);

        if ($esHabitaciones) {
            $nuevaCantidad = (int) $validated['cantidad_habitaciones'];

            if ($nuevaCantidad > $cantidadAnterior) {
                $this->crearHabitaciones($propiedad, $nuevaCantidad, $cantidadAnterior);
            }
        }

        return redirect()->route('admin.propiedades')->with('success', 'Propiedad actualizada correctamente.');
    }

    public function destroyPropiedad($id)
    {
        Propiedad::findOrFail($id)->delete();

        return redirect()->route('admin.propiedades')->with('success', 'Propiedad eliminada correctamente.');
    }

    /**
     * Crea las filas de Habitacion faltantes para una propiedad tipo
     * "habitaciones", numeradas secuencialmente a partir de $desde + 1.
     */
    private function crearHabitaciones(Propiedad $propiedad, int $cantidadTotal, int $desde = 0): void
    {
        for ($numero = $desde + 1; $numero <= $cantidadTotal; $numero++) {
            Habitacion::create([
                'propiedad_id' => $propiedad->id,
                'numero' => (string) $numero,
                'estado' => 'disponible',
            ]);
        }
    }
}
