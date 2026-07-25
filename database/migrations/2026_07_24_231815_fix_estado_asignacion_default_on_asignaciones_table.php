<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El código de la aplicación (InquilinoController, etc.) siempre usa el valor
 * 'activo' para estado_asignacion, pero la migración original definía el
 * default de la columna como 'activa'. Esta migración corrige ese default
 * para que coincida con los valores reales usados en el sistema.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off');

            Schema::create('asignaciones_tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
                $table->foreignId('habitacion_id')->nullable()->constrained('habitaciones')->cascadeOnDelete();
                $table->foreignId('propiedad_id')->nullable()->constrained('propiedades')->cascadeOnDelete();
                $table->date('fecha_inicio');
                $table->date('fecha_fin')->nullable();
                $table->string('estado_asignacion')->default('activo');
                $table->timestamps();
            });

            DB::statement('INSERT INTO asignaciones_tmp (id, usuario_id, habitacion_id, propiedad_id, fecha_inicio, fecha_fin, estado_asignacion, created_at, updated_at)
                SELECT id, usuario_id, habitacion_id, propiedad_id, fecha_inicio, fecha_fin, estado_asignacion, created_at, updated_at FROM asignaciones');

            Schema::drop('asignaciones');
            Schema::rename('asignaciones_tmp', 'asignaciones');

            DB::statement('PRAGMA foreign_keys=on');

            return;
        }

        DB::statement("ALTER TABLE asignaciones MODIFY estado_asignacion VARCHAR(255) NOT NULL DEFAULT 'activo'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // No se revierte: recrear con el default anterior no aporta valor real.
            return;
        }

        DB::statement("ALTER TABLE asignaciones MODIFY estado_asignacion VARCHAR(255) NOT NULL DEFAULT 'activa'");
    }
};
