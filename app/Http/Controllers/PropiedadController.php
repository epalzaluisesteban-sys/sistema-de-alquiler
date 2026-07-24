<?php

namespace App\Http\Controllers;

use App\Models\Propiedad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropiedadController extends Controller
{
    public function propiedades()
    {
        $propiedades = Propiedad::withCount('habitaciones')->orderBy('nombre')->get();

        return view('Propietario.propiedades', compact('propiedades'));
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
        ]);

        Propiedad::create($validated + ['usuario_id' => Auth::user()->id]);

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
        ]);

        $propiedad->update($validated);

        return redirect()->route('admin.propiedades')->with('success', 'Propiedad actualizada correctamente.');
    }

    public function destroyPropiedad($id)
    {
        Propiedad::findOrFail($id)->delete();

        return redirect()->route('admin.propiedades')->with('success', 'Propiedad eliminada correctamente.');
    }
}
