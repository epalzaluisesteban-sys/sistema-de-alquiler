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

            Schema::create('usuarios_tmp', function (Blueprint $table) {
                $table->id();
                $table->string('cedula')->unique();
                $table->string('nombre')->nullable();
                $table->string('apellido')->nullable();
                $table->string('telefono')->nullable();
                $table->string('contrasena');
                $table->string('rol', 20);
                $table->timestamps();
            });

            DB::statement('INSERT INTO usuarios_tmp (id, cedula, nombre, apellido, telefono, contrasena, rol, created_at, updated_at)
                SELECT id, cedula, nombre, apellido, telefono, contrasena, rol, created_at, updated_at FROM usuarios');

            Schema::drop('usuarios');
            Schema::rename('usuarios_tmp', 'usuarios');

            DB::statement('PRAGMA foreign_keys=on');

            return;
        }

        DB::statement("ALTER TABLE usuarios MODIFY rol VARCHAR(20) NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // No se revierte: perderíamos cualquier usuario con rol 'encargado'.
            return;
        }

        DB::statement("ALTER TABLE usuarios MODIFY rol ENUM('propietario','inquilino') NOT NULL");
    }
};
