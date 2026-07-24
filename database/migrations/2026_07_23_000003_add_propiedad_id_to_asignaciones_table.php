<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
                $table->string('estado_asignacion')->default('activa');
                $table->timestamps();
            });

            DB::statement('INSERT INTO asignaciones_tmp (id, usuario_id, habitacion_id, fecha_inicio, fecha_fin, estado_asignacion, created_at, updated_at)
                SELECT id, usuario_id, habitacion_id, fecha_inicio, fecha_fin, estado_asignacion, created_at, updated_at FROM asignaciones');

            Schema::drop('asignaciones');
            Schema::rename('asignaciones_tmp', 'asignaciones');

            DB::statement('PRAGMA foreign_keys=on');

            return;
        }

        Schema::table('asignaciones', function (Blueprint $table) {
            $table->foreignId('propiedad_id')->nullable()->after('habitacion_id')->constrained('propiedades')->cascadeOnDelete();
        });

        DB::statement('ALTER TABLE asignaciones MODIFY habitacion_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // No se revierte: perderíamos las asignaciones de tipo "Casa" (propiedad_id).
            return;
        }

        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('propiedad_id');
        });

        DB::statement('ALTER TABLE asignaciones MODIFY habitacion_id BIGINT UNSIGNED NOT NULL');
    }
};
