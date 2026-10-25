<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagoRequest;
use App\Http\Requests\UpdatePagoRequest;
use App\Models\Asignacion;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            ->with(['asignacion.usuario', 'asignacion.habitacion.propiedad', 'asignacion.propiedad', 'confirmadoPor'])
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

    public function storePago(StorePagoRequest $request)
    {
        Pago::create($this->conAuditoriaConfirmacion($request->validated()));

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

    // La autorización (PagoPolicy::update) la hace UpdatePagoRequest antes de validar.
    public function updatePago(UpdatePagoRequest $request)
    {
        $pago = $request->pago();

        $pago->update($this->conAuditoriaConfirmacion($request->validated(), $pago));

        return redirect()->route('admin.pagos')->with('success', 'Pago actualizado correctamente.');
    }

    public function quickUpdateEstadoPago(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);
        $this->authorize('update', $pago);

        $validated = $request->validate([
            'estado_pago' => ['required', 'in:pendiente,notificado,confirmado'],
        ]);

        $pago->update($this->conAuditoriaConfirmacion($validated, $pago));

        return back()->with('success', 'Estado del pago actualizado.');
    }

    // Soft delete: el pago va a la papelera (solo el Propietario puede
    // verla, restaurarlo o eliminarlo definitivamente). El comprobante se
    // conserva a propósito: sirve de respaldo y permite restaurar el pago.
    public function destroyPago($id)
    {
        $pago = Pago::findOrFail($id);
        $this->authorize('delete', $pago);

        $pago->delete();

        return redirect()->route('admin.pagos')->with('success', 'Pago enviado a la papelera.');
    }

    public function papeleraPagos()
    {
        $pagos = Pago::onlyTrashed()
            ->porPropietario()
            ->with(['asignacion.usuario', 'asignacion.habitacion.propiedad', 'asignacion.propiedad', 'confirmadoPor'])
            ->orderByDesc('deleted_at')
            ->paginate(15);

        return view('Propietario.papelera-pagos', compact('pagos'));
    }

    public function restorePago($id)
    {
        $pago = Pago::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $pago);

        $pago->restore();

        return redirect()->route('admin.pagos-papelera')->with('success', 'Pago restaurado correctamente.');
    }

    public function forceDestroyPago($id)
    {
        $pago = Pago::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $pago);

        $pago->forceDelete();

        // Borrado definitivo: ya nada referencia el comprobante, así que se
        // elimina el archivo para no dejar huérfanos en storage/app/public.
        if ($pago->ruta_comprobante) {
            Storage::disk('public')->delete($pago->ruta_comprobante);
        }

        return redirect()->route('admin.pagos-papelera')->with('success', 'Pago eliminado definitivamente.');
    }

    // Auditoría de confirmación: al pasar a 'confirmado' guarda quién y
    // cuándo; si deja de estar confirmado se limpian, para que esos campos
    // describan siempre la confirmación vigente.
    private function conAuditoriaConfirmacion(array $datos, ?Pago $pago = null): array
    {
        $eraConfirmado = $pago?->estado_pago === 'confirmado';
        $esConfirmado = $datos['estado_pago'] === 'confirmado';

        if ($esConfirmado && ! $eraConfirmado) {
            $datos['confirmado_por'] = auth()->id();
            $datos['confirmado_en'] = now();
        } elseif (! $esConfirmado && $eraConfirmado) {
            $datos['confirmado_por'] = null;
            $datos['confirmado_en'] = null;
        }

        return $datos;
    }
}
