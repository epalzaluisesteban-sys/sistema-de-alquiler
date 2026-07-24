<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\House;
use App\Models\Room;
use App\Models\Tenancy;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Función principal para cargar el panel
    public function index()
    {
        // Más adelante, aquí harás consultas a la base de datos. Ejemplo:
        // $totalInquilinos = User::where('role', 'tenant')->count();
        // $pagosPendientes = Pago::where('estado', 'pendiente')->get();
        
        // Por ahora, solo retornamos la vista que creamos
        return view('admin.dashboard');
    }

    public function inquilinos()
    {
        $inquilinos = Usuario::where('rol', 'inquilino')->get();
        return view('admin.inquilinos', compact('inquilinos'));
    }

    public function pagos()
    {
        return view('admin.pagos');
    }

    public function notificaciones()
    {
        return view('admin.notificaciones');
    }

    public function nuevoAdmin()
    {
        $admins = Usuario::where('rol', 'propietario')->get();
        return view('admin.nuevo-admin', compact('admins'));
    }

    public function nuevoInquilino()
    {
        $houses = House::where('state','Disponible')->get();
        $rooms = Room::where('state','Disponible')->get();
        return view('admin.nuevo-inquilino', compact('houses','rooms'));
    }

    public function editInquilino($id)
    {
        $inquilino = Usuario::findOrFail($id);
        return view('admin.editar-inquilino', compact('inquilino'));
    }

    public function updateInquilino(Request $request, $id)
    {
        $inquilino = Usuario::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'cedula' => 'required|string|max:20|unique:usuarios,cedula,'.$id,
        ]);

        $inquilino->update([
            'name' => $request->name,
            'cedula' => $request->cedula,
            'email' => null,
        ]);

        return redirect()->route('admin.inquilinos')->with('success', 'Datos del inquilino actualizados.');
    }

    public function storeInquilino(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cedula' => 'required|string|max:20|unique:usuarios',
            'password' => 'required|string|min:6',
        ]);

        $user = Usuario::create([
            'name' => $request->name,
            'cedula' => $request->cedula,
            'email' => null,
            'password' => $request->password,
            'role' => 'tenant',
        ]);

        // Si vienen datos de alquiler, crear la tenencia
        if ($request->filled('rental_type') && $request->filled('property_id')) {
            $type = $request->rental_type; // 'Casa' o 'Habitación'
            if ($type === 'Casa') {
                $house = House::find($request->property_id);
                if ($house) {
                    Tenancy::create([
                        'payable_id' => $house->id,
                        'payable_type' => House::class,
                        'tenant_id' => $user->id,
                        'start_date' => now()->toDateString(),
                        'active' => true,
                    ]);
                    $house->update(['state' => 'Ocupada']);
                }
            } elseif ($type === 'Habitación') {
                $room = Room::find($request->property_id);
                if ($room) {
                    Tenancy::create([
                        'payable_id' => $room->id,
                        'payable_type' => Room::class,
                        'tenant_id' => $user->id,
                        'start_date' => now()->toDateString(),
                        'active' => true,
                    ]);
                    $room->update(['state' => 'Ocupada']);
                }
            }
        }

        return redirect()->route('admin.inquilinos')->with('success', 'Inquilino registrado con éxito.');
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cedula' => 'required|string|max:20|unique:usuarios',
            'password' => 'required|string|min:6',
        ]);

        Usuario::create([
            'name' => $request->name,
            'cedula' => $request->cedula,
            'email' => null,
            'password' => $request->password,
            'role' => 'admin',
        ]);

        return redirect()->route('admin.nuevo-admin')->with('success', 'Administrador registrado con éxito.');
    }

    public function destroyAdmin($id)
    {
        $user = Usuario::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.nuevo-admin')->with('success', 'Administrador eliminado correctamente.');
    }

    public function destroyInquilino($id)
    {
        $user = Usuario::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.inquilinos')->with('success', 'Inquilino eliminado correctamente.');
    }

    // Métodos para Gestión de Residencias
    public function residencias()
    {
        // En el futuro, aquí consultarás tu modelo Residencia/Habitacion
        // $residencias = Residencia::all();
        return view('admin.residencias');
    }

    public function nuevaResidencia()
    {
        return view('admin.nueva-residencia');
    }

    public function storeResidencia(Request $request)
    {
        // Lógica de validación y guardado aquí
        return redirect()->route('admin.residencias')->with('success', 'Residencia registrada correctamente.');
    }
}