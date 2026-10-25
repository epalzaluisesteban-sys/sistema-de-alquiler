<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function pagos(Request $request)
    {
        $estado = $request->query('estado');
        $tipoPago = $request->query('tipo_pago');
        $q = trim((string) $request->query('q', ''));
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $pagos = Pago::porPropietario()
            ->with(['asignacion.usuario', 'asignacion.habitacion.propiedad', 'asignacion.propiedad'])
            ->when($estado, fn ($query) => $query->where('estado_pago', $estado))
            ->when($tipoPago, fn ($query) => $query->where('tipo_pago', $tipoPago))
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

        return view('panel.pagos', compact('pagos', 'estado', 'tipoPago', 'q', 'desde', 'hasta'));
    }

    public function nuevoPago(Request $request)
    {
        $asignaciones = Asignacion::porPropietario()
            ->where('estado_asignacion', 'activo')
            ->with(['usuario', 'habitacion.propiedad', 'propiedad'])
            ->get();

        // Permite llegar acá desde "Registrar pago" en la lista de
        // Inquilinos con la asignación de ese inquilino ya preseleccionada.
        $preseleccionada = $request->query('asignacion');

        return view('panel.nuevo-pago', compact('asignaciones', 'preseleccionada'));
    }

    public function storePago(Request $request)
    {
        $validated = $request->validate([
            'asignacion_id' => ['required', 'exists:asignaciones,id', $this->asignacionDelPropietario()],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'estado_pago' => ['required', 'in:pendiente,notificado,confirmado'],
            'tipo_pago' => ['required', 'in:divisas,pago_movil'],
        ]);

        Pago::create($validated);

        return redirect()->route('admin.pagos')->with('success', 'Pago registrado correctamente.');
    }

    public function editPago($id)
    {
        $pago = Pago::findOrFail($id);
        $this->authorize('update', $pago);

        $asignaciones = Asignacion::porPropietario()
            ->where(fn ($q) => $q->where('estado_asignacion', 'activo')->orWhere('id', $pago->asignacion_id))
            ->with(['usuario', 'habitacion.propiedad', 'propiedad'])
            ->get();

        return view('panel.editar-pago', compact('pago', 'asignaciones'));
    }

    public function updatePago(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);
        $this->authorize('update', $pago);

        $validated = $request->validate([
            'asignacion_id' => ['required', 'exists:asignaciones,id', $this->asignacionDelPropietario()],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'estado_pago' => ['required', 'in:pendiente,notificado,confirmado'],
            'tipo_pago' => ['required', 'in:divisas,pago_movil'],
        ]);

        $pago->update($validated);

        return redirect()->route('admin.pagos')->with('success', 'Pago actualizado correctamente.');
    }

    public function quickUpdateEstadoPago(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);
        $this->authorize('update', $pago);

        $validated = $request->validate([
            'estado_pago' => ['required', 'in:pendiente,notificado,confirmado'],
        ]);

        $pago->update($validated);

        return back()->with('success', 'Estado del pago actualizado.');
    }

    public function destroyPago($id)
    {
        $pago = Pago::findOrFail($id);
        $this->authorize('delete', $pago);

        $pago->delete();

        return redirect()->route('admin.pagos')->with('success', 'Pago eliminado correctamente.');
    }

    // Regla de validación: la asignación elegida en el formulario debe ser
    // de una propiedad del propietario actual (evita registrar o mover un
    // pago hacia el alquiler de otro propietario manipulando el select).
    private function asignacionDelPropietario(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) {
            if (! Asignacion::porPropietario()->whereKey($value)->exists()) {
                $fail('La asignación seleccionada no es válida.');
            }
        };
    }
}
