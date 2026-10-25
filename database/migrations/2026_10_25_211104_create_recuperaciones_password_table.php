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
        Schema::create('recuperaciones_password', function (Blueprint $table) {
            $table->id();
            // Relación "genera": Usuario 1 --- N RecuperacionPassword
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            // Se guarda el hash del token, nunca el valor en texto plano (igual
            // que la tabla password_reset_tokens por defecto de Laravel): si la
            // BD se filtra, el token no sirve para resetear nada.
            $table->string('token');
            $table->timestamp('expira_en');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recuperaciones_password');
    }
};
