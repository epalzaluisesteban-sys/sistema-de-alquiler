<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Propietario al que pertenece un Encargado: define qué propiedades
        // (y por extensión inquilinos/pagos/reportes) puede gestionar. Es
        // null para propietarios; los inquilinos no lo necesitan porque su
        // dueño se deriva de sus asignaciones.
        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('propietario_id')->nullable()->after('rol')
                ->constrained('usuarios')->nullOnDelete();
        });

        // Backfill de encargados existentes: primero se toma el dueño de la
        // propiedad donde viven (si tienen asignación); si no, y en el
        // sistema hay un único propietario, se les asigna ese.
        $propietarios = DB::table('usuarios')->where('rol', 'propietario')->pluck('id');

        DB::table('usuarios')->where('rol', 'encargado')->whereNull('propietario_id')->pluck('id')
            ->each(function ($encargadoId) use ($propietarios) {
                $asignacion = DB::table('asignaciones')
                    ->where('usuario_id', $encargadoId)
                    ->orderByDesc('fecha_inicio')
                    ->first();

                $propietarioId = null;

                if ($asignacion?->propiedad_id) {
                    $propietarioId = DB::table('propiedades')->where('id', $asignacion->propiedad_id)->value('usuario_id');
                } elseif ($asignacion?->habitacion_id) {
                    $propietarioId = DB::table('habitaciones')
                        ->join('propiedades', 'propiedades.id', '=', 'habitaciones.propiedad_id')
                        ->where('habitaciones.id', $asignacion->habitacion_id)
                        ->value('propiedades.usuario_id');
                }

                if (! $propietarioId && $propietarios->count() === 1) {
                    $propietarioId = $propietarios->first();
                }

                if ($propietarioId) {
                    DB::table('usuarios')->where('id', $encargadoId)->update(['propietario_id' => $propietarioId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('propietario_id');
        });
    }
};
