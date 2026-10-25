<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthCedulaTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_cedula(): void
    {
        $response = $this->post('/login', [
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors(['cedula']);
    }

    public function test_login_works_with_cedula(): void
    {
        $user = Usuario::create([
            'name' => 'Administrador',
            'cedula' => '12345678',
            'password' => 'secret123',
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'cedula' => '12345678',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_seeded_test_users_can_log_in_with_their_cedula(): void
    {
        $this->seed(\Database\Seeders\UsuarioSeeder::class);
        $this->seed(\Database\Seeders\UsuarioSeeder::class);

        $this->assertDatabaseCount('usuarios', 2);

        $this->post('/login', [
            'cedula' => 'V-32267986',
            'password' => 'luis123',
        ])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $this->post('/logout');

        $this->post('/login', [
            'cedula' => 'V-32267995',
            'password' => 'soto123',
        ])->assertRedirect(route('tenant.dashboard'));
        $this->assertAuthenticated();
    }
}
