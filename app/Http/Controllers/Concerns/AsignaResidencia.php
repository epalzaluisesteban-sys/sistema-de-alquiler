<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Asignacion;
use App\Models\Habitacion;
use App\Models\Propiedad;
use App\Models\Usuario;

trait AsignaResidencia
{
    /**
     * Crea la Asignacion que liga a un usuario (inquilino o encargado) con
     * una Propiedad completa ("Casa") o con una Habitacion puntual, y marca
     * esa residencia como ocupada. Solo acepta residencias del propietario
     * actual (devuelve null si el id es de otro propietario).
     */
    private function asignarResidencia(
        Usuario $usuario,
        string $tipo,
        int $id,
        ?float $precio = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null
    ): ?Asignacion {
        $fechaInicio ??= now()->toDateString();

        if ($tipo === 'Casa') {
            $propiedad = Propiedad::porPropietario()->find($id);

            if (! $propiedad) {
                return null;
            }

            $asignacion = Asignacion::create([
                'usuario_id' => $usuario->id,
                'propiedad_id' => $propiedad->id,
                'precio' => $precio ?? $propiedad->precio_acordado,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado_asignacion' => 'activo',
            ]);

            $propiedad->update(['estado' => 'ocupada']);

            return $asignacion;
        }

        $habitacion = Habitacion::porPropietario()->find($id);

        if (! $habitacion) {
            return null;
        }

        $asignacion = Asignacion::create([
            'usuario_id' => $usuario->id,
            'habitacion_id' => $habitacion->id,
            'precio' => $precio ?? $habitacion->precio_actual,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'estado_asignacion' => 'activo',
        ]);

        $habitacion->update(['estado' => 'ocupada']);

        return $asignacion;
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
}
