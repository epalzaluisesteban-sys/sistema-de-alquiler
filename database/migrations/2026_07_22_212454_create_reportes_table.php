<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            // Relación "genera": Asignacion 1 --- N Reporte
            $table->foreignId('asignacion_id')->constrained('asignaciones')->cascadeOnDelete();
            $table->text('descripcion');
            $table->date('fecha');
            $table->string('estado_reporte')->default('pendiente'); // pendiente | en_revision | resuelto
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};