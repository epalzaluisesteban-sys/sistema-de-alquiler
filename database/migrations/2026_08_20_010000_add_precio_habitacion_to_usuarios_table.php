<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Precio mensual único que el propietario define en Ajustes para
            // todas sus habitaciones; se asigna automáticamente al registrar
            // cada habitación en lugar de pedirlo en ese formulario.
            $table->decimal('precio_habitacion', 10, 2)->nullable()->after('tema_boton');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('precio_habitacion');
        });
    }
};
