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
            // Presets de personalización visual del panel (nulos = valores por
            // defecto actuales); las claves válidas viven en App\Support\Personalizacion.
            $table->string('tema_fondo')->nullable()->after('rol');
            $table->string('tema_color')->nullable()->after('tema_fondo');
            $table->string('tema_fuente')->nullable()->after('tema_color');
            $table->string('tema_boton')->nullable()->after('tema_fuente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['tema_fondo', 'tema_color', 'tema_fuente', 'tema_boton']);
        });
    }
};
