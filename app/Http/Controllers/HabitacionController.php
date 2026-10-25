<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Propiedad;
use Illuminate\Http\Request;

class HabitacionController extends Controller
{
    public function habitaciones(Request $request)
    {
        $propiedad = $request->filled('propiedad_id')
            ? Propiedad::find($request->integer('propiedad_id'))
            : null;

        $habitaciones = Habitacion::with([
                'propiedad.usuario',
                'asignaciones' => fn ($q) => $q->where('estado_asignacion', 'activo'),
            ])
            ->when($propiedad, fn ($q) => $q->where('propiedad_id', $propiedad->id))
            ->orderBy('propiedad_id')
            ->orderByRaw('CAST(numero AS UNSIGNED)')
            ->get();

        return view('Propietario.habitaciones', compact('habitaciones', 'propiedad'));
    }

    public function updateEstado(Request $request, $id)
    {
        $habitacion = Habitacion::findOrFail($id);

        $validated = $request->validate([
            'estado' => ['required', 'in:disponible,ocupada,mantenimiento'],
        ]);

        $habitacion->update($validated);

        return redirect()->route('admin.habitaciones')->with('success', 'Estado de la habitación actualizado.');
    }
}
