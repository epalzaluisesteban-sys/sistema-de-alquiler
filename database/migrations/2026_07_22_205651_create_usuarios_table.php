<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('cedula')->unique();
            $table->string('nombre')->nullable();
            $table->string('apellido')->nullable();
            $table->string('telefono')->nullable();
            // Nota: si usas el sistema de Auth de Laravel, considera renombrar
            // esta columna a "password" para aprovechar el hashing automático
            // y los guards de autenticación que trae Laravel por defecto.
            $table->string('contrasena');
            $table->enum('rol', ['propietario', 'inquilino']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};