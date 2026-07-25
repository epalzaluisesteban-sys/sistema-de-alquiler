<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function quickUpdateEstadoReporte(Request $request, $id)
    {
        $validated = $request->validate([
            'estado_reporte' => ['required', 'in:pendiente,en_revision,resuelto'],
        ]);

        $reporte = Reporte::findOrFail($id);
        $reporte->update(['estado_reporte' => $validated['estado_reporte']]);

        return redirect()->route('admin.notificaciones')->with('success', 'Estado del reporte actualizado.');
    }
}
