<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use Illuminate\Http\Request;

class HabitacionController extends Controller
{
    public function habitaciones()
    {
        $habitaciones = Habitacion::with([
                'propiedad.usuario',
                'asignaciones' => fn ($q) => $q->where('estado_asignacion', 'activo'),
            ])
            ->orderBy('propiedad_id')
            ->orderByRaw('CAST(numero AS UNSIGNED)')
            ->get();

        return view('Propietario.habitaciones', compact('habitaciones'));
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
