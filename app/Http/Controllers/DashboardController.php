<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EsEncargado;
use App\Models\Asignacion;
use App\Models\Pago;
use App\Models\Reporte;

class DashboardController extends Controller
{
    use EsEncargado;

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
