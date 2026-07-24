<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EsEncargado;
use App\Models\Asignacion;
use App\Models\Pago;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    use EsEncargado;

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
}
