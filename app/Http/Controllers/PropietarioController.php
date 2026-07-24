<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Habitacion;
use App\Models\Pago;
use App\Models\Propiedad;
use App\Models\Reporte;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PropietarioController extends Controller
{
    private function esEncargado(): bool
    {
        return strtolower(trim(Auth::user()->role ?? '')) === 'encargado';
    }

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

    public function index()
    {
        $inquilinosActivos = Asignacion::where('estado_asignacion', 'activo')->distinct('usuario_id')->count('usuario_id');
        $pagosPendientes = Pago::whereIn('estado_pago', ['pendiente', 'notificado'])->count();
        $ingresosMes = Pago::where('estado_pago', 'confirmado')
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('monto');

        $ultimosMovimientos = Pago::with(['asignacion.usuario', 'asignacion.habitacion.propiedad', 'asignacion.propiedad'])
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $data = compact('inquilinosActivos', 'pagosPendientes', 'ingresosMes', 'ultimosMovimientos');

        return view($this->esEncargado() ? 'Encargado.dashboard' : 'Propietario.dashboard', $data);
    }

    // ---------------------------------------------------------------
    // Inquilinos
    // ---------------------------------------------------------------

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

    // ---------------------------------------------------------------
    // Propiedades
    // ---------------------------------------------------------------

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

    // ---------------------------------------------------------------
    // Habitaciones
    // ---------------------------------------------------------------

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

    // ---------------------------------------------------------------
    // Encargados (exclusivo del Propietario)
    // ---------------------------------------------------------------

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

    // ---------------------------------------------------------------
    // Pagos
    // ---------------------------------------------------------------

    public function pagos(Request $request)
    {
        $estado = $request->query('estado');
        $q = trim((string) $request->query('q', ''));
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $pagos = Pago::with(['asignacion.usuario', 'asignacion.habitacion.propiedad', 'asignacion.propiedad'])
            ->when($estado, fn ($query) => $query->where('estado_pago', $estado))
            ->when($desde, fn ($query) => $query->whereDate('fecha', '>=', $desde))
            ->when($hasta, fn ($query) => $query->whereDate('fecha', '<=', $hasta))
            ->when($q !== '', function ($query) use ($q) {
                $palabras = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);

                $query->whereHas('asignacion', function ($aq) use ($q, $palabras) {
                    $aq->whereHas('usuario', function ($uq) use ($q, $palabras) {
                        $uq->where('nombre', 'like', "%{$q}%")
                            ->orWhere('apellido', 'like', "%{$q}%")
                            ->orWhere('cedula', 'like', "%{$q}%");

                        // Permite buscar "Nombre Apellido" aunque estén en columnas separadas.
                        if (count($palabras) > 1) {
                            $uq->orWhere(function ($nombreCompleto) use ($palabras) {
                                foreach ($palabras as $palabra) {
                                    $nombreCompleto->where(function ($w) use ($palabra) {
                                        $w->where('nombre', 'like', "%{$palabra}%")
                                            ->orWhere('apellido', 'like', "%{$palabra}%");
                                    });
                                }
                            });
                        }
                    })
                    ->orWhereHas('propiedad', fn ($pq) => $pq->where('nombre', 'like', "%{$q}%"))
                    ->orWhereHas('habitacion.propiedad', fn ($pq) => $pq->where('nombre', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view($this->esEncargado() ? 'Encargado.pagos' : 'Propietario.pagos', compact('pagos', 'estado', 'q', 'desde', 'hasta'));
    }

    public function nuevoPago()
    {
        $asignaciones = Asignacion::where('estado_asignacion', 'activo')
            ->with(['usuario', 'habitacion.propiedad', 'propiedad'])
            ->get();

        return view($this->esEncargado() ? 'Encargado.nuevo-pago' : 'Propietario.nuevo-pago', compact('asignaciones'));
    }

    public function storePago(Request $request)
    {
        $validated = $request->validate([
            'asignacion_id' => ['required', 'exists:asignaciones,id'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha' => ['required', 'date'],
            'estado_pago' => ['required', 'in:pendiente,notificado,confirmado'],
        ]);

        Pago::create($validated);

        return redirect()->route('admin.pagos')->with('success', 'Pago registrado correctamente.');
    }

    public function editPago($id)
    {
        $pago = Pago::findOrFail($id);

        $asignaciones = Asignacion::where('estado_asignacion', 'activo')
            ->orWhere('id', $pago->asignacion_id)
            ->with(['usuario', 'habitacion.propiedad', 'propiedad'])
            ->get();

        return view($this->esEncargado() ? 'Encargado.editar-pago' : 'Propietario.editar-pago', compact('pago', 'asignaciones'));
    }

    public function updatePago(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);

        $validated = $request->validate([
            'asignacion_id' => ['required', 'exists:asignaciones,id'],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha' => ['required', 'date'],
            'estado_pago' => ['required', 'in:pendiente,notificado,confirmado'],
        ]);

        $pago->update($validated);

        return redirect()->route('admin.pagos')->with('success', 'Pago actualizado correctamente.');
    }

    public function quickUpdateEstadoPago(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);

        $validated = $request->validate([
            'estado_pago' => ['required', 'in:pendiente,notificado,confirmado'],
        ]);

        $pago->update($validated);

        return back()->with('success', 'Estado del pago actualizado.');
    }

    public function destroyPago($id)
    {
        Pago::findOrFail($id)->delete();

        return redirect()->route('admin.pagos')->with('success', 'Pago eliminado correctamente.');
    }

    // ---------------------------------------------------------------
    // Notificaciones
    // ---------------------------------------------------------------

    public function notificaciones()
    {
        $reportes = Reporte::whereIn('estado_reporte', ['pendiente', 'en_revision'])
            ->with(['asignacion.usuario', 'asignacion.habitacion.propiedad', 'asignacion.propiedad'])
            ->orderByDesc('fecha')
            ->get();

        $avisosPago = Pago::whereIn('estado_pago', ['pendiente', 'notificado'])
            ->with(['asignacion.usuario', 'asignacion.habitacion.propiedad', 'asignacion.propiedad'])
            ->orderByDesc('fecha')
            ->get();

        return view($this->esEncargado() ? 'Encargado.notificaciones' : 'Propietario.notificaciones', compact('reportes', 'avisosPago'));
    }
}
