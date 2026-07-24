<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Crea el Administrador con el rol exacto
        Usuario::create([
            'cedula' => '32267986',
            'nombre' => 'Luis',
            'apellido' => 'Garzon',
            'telefono' => '04120741819',
            'contrasena' => Hash::make('luis123'), // Esta será tu contraseña
            'rol' => 'propietario',
        ]);

        // Crea un Inquilino de prueba
        Usuario::create([
            'cedula' => '32267995',
            'nombre' => 'Ángel',
            'apellido' => 'Soto',
            'telefono' => '04120145180',
            'contrasena' => Hash::make('soto123'), // Esta será tu contraseña
            'rol' => 'inquilino',
        ]);
    }
}