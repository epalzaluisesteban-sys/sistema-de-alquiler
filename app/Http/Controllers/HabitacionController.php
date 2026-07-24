<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Propiedad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HabitacionController extends Controller
{
    public function habitaciones()
    {
        $habitaciones = Habitacion::with('propiedad')->orderBy('numero')->get();

        return view('Propietario.habitaciones', compact('habitaciones'));
    }

    public function nuevaHabitacion()
    {
        $propiedades = Propiedad::orderBy('nombre')->get();

        return view('Propietario.nueva-habitacion', compact('propiedades'));
    }

    public function storeHabitacion(Request $request)
    {
        $validated = $request->validate([
            'propiedad_id' => ['required', 'exists:propiedades,id'],
            'numero' => [
                'required', 'string', 'max:50',
                Rule::unique('habitaciones')->where(fn ($q) => $q->where('propiedad_id', $request->propiedad_id)),
            ],
            'precio' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', 'in:disponible,ocupada,mantenimiento'],
        ]);

        Habitacion::create($validated);

        return redirect()->route('admin.habitaciones')->with('success', 'Habitación registrada correctamente.');
    }

    public function editHabitacion($id)
    {
        $habitacion = Habitacion::findOrFail($id);
        $propiedades = Propiedad::orderBy('nombre')->get();

        return view('Propietario.editar-habitacion', compact('habitacion', 'propiedades'));
    }

    public function updateHabitacion(Request $request, $id)
    {
        $habitacion = Habitacion::findOrFail($id);

        $validated = $request->validate([
            'propiedad_id' => ['required', 'exists:propiedades,id'],
            'numero' => [
                'required', 'string', 'max:50',
                Rule::unique('habitaciones')->where(fn ($q) => $q->where('propiedad_id', $request->propiedad_id))->ignore($habitacion->id),
            ],
            'precio' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', 'in:disponible,ocupada,mantenimiento'],
        ]);

        $habitacion->update($validated);

        return redirect()->route('admin.habitaciones')->with('success', 'Habitación actualizada correctamente.');
    }

    public function destroyHabitacion($id)
    {
        Habitacion::findOrFail($id)->delete();

        return redirect()->route('admin.habitaciones')->with('success', 'Habitación eliminada correctamente.');
    }
}
