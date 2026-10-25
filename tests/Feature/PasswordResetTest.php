<?php

namespace Tests\Feature;

use App\Models\RecuperacionPassword;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(): Usuario
    {
        return Usuario::create([
            'cedula' => 'V-11111111',
            'nombre' => 'Ana',
            'apellido' => 'Pérez',
            'telefono' => '04141234567',
            'password' => 'vieja123',
            'rol' => 'propietario',
        ]);
    }

    public function test_cedula_y_telefono_incorrectos_no_generan_token(): void
    {
        $this->crearUsuario();

        $this->post('/olvide-password', [
            'cedula' => 'V-11111111',
            'telefono' => '0000000000',
        ])->assertSessionHasErrors(['cedula']);

        $this->assertDatabaseCount('recuperaciones_password', 0);
    }

    public function test_cedula_y_telefono_correctos_generan_enlace_de_reset(): void
    {
        $usuario = $this->crearUsuario();

        $response = $this->post('/olvide-password', [
            'cedula' => 'V-11111111',
            'telefono' => '04141234567',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('recuperaciones_password', 1);
        $this->assertStringContainsString(
            "/olvide-password/{$usuario->id}/",
            $response->headers->get('Location')
        );
    }

    public function test_puede_establecer_una_nueva_contrasena_con_el_token_valido(): void
    {
        $usuario = $this->crearUsuario();

        $location = $this->post('/olvide-password', [
            'cedula' => 'V-11111111',
            'telefono' => '04141234567',
        ])->headers->get('Location');

        $token = basename($location);

        $this->post("/olvide-password/{$usuario->id}/{$token}", [
            'password' => 'nueva123',
            'password_confirmation' => 'nueva123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('nueva123', $usuario->refresh()->contrasena));
        $this->assertDatabaseCount('recuperaciones_password', 0);
    }

    public function test_un_token_ya_usado_no_sirve_dos_veces(): void
    {
        $usuario = $this->crearUsuario();

        $this->post('/olvide-password', [
            'cedula' => 'V-11111111',
            'telefono' => '04141234567',
        ]);

        $token = RecuperacionPassword::first();
        $plain = 'token-de-prueba';
        $token->update(['token' => \Illuminate\Support\Facades\Hash::make($plain)]);

        $this->post("/olvide-password/{$usuario->id}/{$plain}", [
            'password' => 'nueva123',
            'password_confirmation' => 'nueva123',
        ])->assertRedirect(route('login'));

        // Reusar el mismo enlace una segunda vez debe fallar porque ya se borró.
        $this->post("/olvide-password/{$usuario->id}/{$plain}", [
            'password' => 'otra123',
            'password_confirmation' => 'otra123',
        ])->assertRedirect(route('password.request'));
    }

    public function test_un_token_expirado_no_permite_resetear(): void
    {
        $usuario = $this->crearUsuario();
        $plain = 'token-expirado';

        RecuperacionPassword::create([
            'usuario_id' => $usuario->id,
            'token' => \Illuminate\Support\Facades\Hash::make($plain),
            'expira_en' => now()->subMinute(),
        ]);

        $this->get("/olvide-password/{$usuario->id}/{$plain}")
            ->assertRedirect(route('password.request'));
    }
}
