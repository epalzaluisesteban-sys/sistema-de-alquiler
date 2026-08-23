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
        Schema::table('propiedades', function (Blueprint $table) {
            // Precio de alquiler acordado para propiedades que se rentan
            // completas (sin habitaciones); se ingresa al registrar la
            // propiedad. No aplica a propiedades con habitaciones, cuyo
            // precio se maneja por habitación.
            $table->decimal('precio_acordado', 10, 2)->nullable()->after('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropColumn('precio_acordado');
        });
    }
};
