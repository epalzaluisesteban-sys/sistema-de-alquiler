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
        Schema::table('asignaciones', function (Blueprint $table) {
            // Precio de alquiler acordado para este contrato, fijado al
            // registrar al inquilino (para Habitación se prellena con el
            // precio de la habitación, para Casa se ingresa manualmente).
            $table->decimal('precio', 10, 2)->nullable()->after('propiedad_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropColumn('precio');
        });
    }
};
