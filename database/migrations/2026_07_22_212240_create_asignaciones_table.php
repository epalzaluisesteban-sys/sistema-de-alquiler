<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            // Relación "ocupa": Usuario 1 --- N Asignacion
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            // Relación "asignada": Habitacion 1 --- N Asignacion
            $table->foreignId('habitacion_id')->constrained('habitaciones')->cascadeOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('estado_asignacion')->default('activa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};