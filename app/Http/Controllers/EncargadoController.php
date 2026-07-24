<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class EncargadoController extends Controller
{
    public function encargados()
    {
        $encargados = Usuario::where('rol', 'encargado')->orderBy('nombre')->get();

        return view('Propietario.encargados', compact('encargados'));
    }

    public function nuevoEncargado()
    {
        return view('Propietario.nuevo-encargado');
    }

    public function storeEncargado(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'cedula' => ['required', 'string', 'max:20', 'unique:usuarios,cedula'],
            'telefono' => ['required', 'string', 'max:20'],
            'contrasena' => ['required', 'string', 'min:6'],
        ]);

        Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'cedula' => $request->cedula,
            'telefono' => $request->telefono,
            'password' => $request->contrasena,
            'role' => 'encargado',
        ]);

        return redirect()->route('admin.encargados')->with('success', 'Encargado registrado con éxito.');
    }

    public function editEncargado($id)
    {
        $encargado = Usuario::where('rol', 'encargado')->findOrFail($id);

        return view('Propietario.editar-encargado', compact('encargado'));
    }

    public function updateEncargado(Request $request, $id)
    {
        $encargado = Usuario::where('rol', 'encargado')->findOrFail($id);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'cedula' => ['required', 'string', 'max:20', 'unique:usuarios,cedula,' . $id],
            'telefono' => ['required', 'string', 'max:20'],
            'contrasena' => ['nullable', 'string', 'min:6'],
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

        return redirect()->route('admin.encargados')->with('success', 'Encargado actualizado correctamente.');
    }

    public function destroyEncargado($id)
    {
        Usuario::where('rol', 'encargado')->findOrFail($id)->delete();

        return redirect()->route('admin.encargados')->with('success', 'Encargado eliminado correctamente.');
    }
}
