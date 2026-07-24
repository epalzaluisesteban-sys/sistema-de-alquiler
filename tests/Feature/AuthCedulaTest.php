<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
}
