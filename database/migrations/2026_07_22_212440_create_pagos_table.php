<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            // Relación "genera": Asignacion 1 --- N Pago
            $table->foreignId('asignacion_id')->constrained('asignaciones')->cascadeOnDelete();
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->string('estado_pago')->default('pendiente'); // pendiente | notificado | confirmado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};