<?php

namespace Tests\Feature;

use App\Models\Asignacion;
use App\Models\Pago;
use App\Models\Propiedad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Con más de un Propietario en el sistema, cada uno (y sus Encargados)
 * solo puede ver y modificar los inquilinos/pagos de sus propias propiedades.
 */
class OwnershipTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $propietarioA;
    private Usuario $propietarioB;
    private Usuario $inquilinoA;
    private Pago $pagoA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->propietarioA = $this->crearUsuario('V-10000001', 'propietario');
        $this->propietarioB = $this->crearUsuario('V-20000002', 'propietario');

        $this->inquilinoA = $this->crearUsuario('V-30000003', 'inquilino', 'Ana Alquila');
        $this->pagoA = $this->crearAlquilerConPago($this->propietarioA, $this->inquilinoA);
    }

    public function test_propietario_b_no_ve_pagos_del_propietario_a(): void
    {
        $inquilinoB = $this->crearUsuario('V-40000004', 'inquilino', 'Beto Renta');
        $pagoB = $this->crearAlquilerConPago($this->propietarioB, $inquilinoB);

        $this->actingAs($this->propietarioB)
            ->get(route('admin.pagos'))
            ->assertOk()
            ->assertViewHas('pagos', fn ($pagos) => $pagos->pluck('id')->all() === [$pagoB->id]);
    }

    public function test_propietario_b_no_puede_editar_ni_borrar_pagos_del_propietario_a(): void
    {
        $this->actingAs($this->propietarioB);

        $this->get(route('admin.edit-pago', $this->pagoA->id))->assertForbidden();

        $this->put(route('admin.update-pago', $this->pagoA->id), [
            'asignacion_id' => $this->pagoA->asignacion_id,
            'monto' => 1,
            'fecha' => now()->toDateString(),
            'estado_pago' => 'confirmado',
            'tipo_pago' => 'divisas',
        ])->assertForbidden();

        $this->patch(route('admin.pagos.quick-estado', $this->pagoA->id), [
            'estado_pago' => 'confirmado',
        ])->assertForbidden();

        $this->delete(route('admin.destroy-pago', $this->pagoA->id))->assertForbidden();

        $this->assertDatabaseHas('pagos', [
            'id' => $this->pagoA->id,
            'monto' => 100,
            'estado_pago' => 'pendiente',
        ]);
    }

    public function test_propietario_b_no_puede_registrar_pagos_en_asignaciones_del_propietario_a(): void
    {
        $this->actingAs($this->propietarioB)
            ->post(route('admin.store-pago'), [
                'asignacion_id' => $this->pagoA->asignacion_id,
                'monto' => 50,
                'fecha' => now()->toDateString(),
                'estado_pago' => 'confirmado',
                'tipo_pago' => 'divisas',
            ])
            ->assertSessionHasErrors('asignacion_id');

        $this->assertDatabaseCount('pagos', 1);
    }

    public function test_propietario_a_si_puede_actualizar_sus_pagos(): void
    {
        $this->actingAs($this->propietarioA)
            ->patch(route('admin.pagos.quick-estado', $this->pagoA->id), [
                'estado_pago' => 'confirmado',
            ])
            ->assertRedirect();

        $this->assertSame('confirmado', $this->pagoA->fresh()->estado_pago);
    }

    public function test_propietario_b_no_ve_ni_gestiona_inquilinos_del_propietario_a(): void
    {
        $this->actingAs($this->propietarioB);

        $this->get(route('admin.inquilinos'))
            ->assertOk()
            ->assertViewHas('inquilinos', fn ($inquilinos) => $inquilinos->isEmpty());

        $this->get(route('admin.edit-inquilino', $this->inquilinoA->id))->assertNotFound();
        $this->get(route('admin.historial-inquilino', $this->inquilinoA->id))->assertNotFound();

        $this->put(route('admin.update-inquilino', $this->inquilinoA->id), [
            'nombre' => 'Hackeado',
            'apellido' => 'Hackeado',
            'cedula' => 'V-30000003',
        ])->assertNotFound();

        $this->patch(route('admin.desalojar-inquilino', $this->inquilinoA->id))->assertNotFound();

        $this->assertSame('Ana', $this->inquilinoA->fresh()->nombre);
        $this->assertDatabaseHas('asignaciones', [
            'usuario_id' => $this->inquilinoA->id,
            'estado_asignacion' => 'activo',
        ]);
    }

    public function test_encargado_gestiona_solo_lo_de_su_propietario(): void
    {
        $encargadoA = $this->crearUsuario('V-50000005', 'encargado');
        $encargadoA->update(['propietario_id' => $this->propietarioA->id]);

        $inquilinoB = $this->crearUsuario('V-40000004', 'inquilino', 'Beto Renta');
        $pagoB = $this->crearAlquilerConPago($this->propietarioB, $inquilinoB);

        $this->actingAs($encargadoA);

        $this->get(route('admin.pagos'))
            ->assertOk()
            ->assertViewHas('pagos', fn ($pagos) => $pagos->pluck('id')->all() === [$this->pagoA->id]);

        $this->patch(route('admin.pagos.quick-estado', $this->pagoA->id), ['estado_pago' => 'confirmado'])
            ->assertRedirect();
        $this->patch(route('admin.pagos.quick-estado', $pagoB->id), ['estado_pago' => 'confirmado'])
            ->assertForbidden();
    }

    public function test_encargado_sin_propietario_no_ve_nada(): void
    {
        $huerfano = $this->crearUsuario('V-60000006', 'encargado');

        $this->actingAs($huerfano)
            ->get(route('admin.pagos'))
            ->assertOk()
            ->assertViewHas('pagos', fn ($pagos) => $pagos->isEmpty());
    }

    private function crearUsuario(string $cedula, string $rol, string $nombre = 'Usuario Prueba'): Usuario
    {
        return Usuario::create([
            'name' => $nombre,
            'cedula' => $cedula,
            'password' => 'secret123',
            'rol' => $rol,
        ]);
    }

    private function crearAlquilerConPago(Usuario $propietario, Usuario $inquilino): Pago
    {
        $propiedad = Propiedad::create([
            'usuario_id' => $propietario->id,
            'nombre' => 'Casa de ' . $propietario->cedula,
            'estado' => 'ocupada',
            'tipo' => 'completa',
            'precio_acordado' => 100,
        ]);

        $asignacion = Asignacion::create([
            'usuario_id' => $inquilino->id,
            'propiedad_id' => $propiedad->id,
            'precio' => 100,
            'fecha_inicio' => now()->subMonth()->toDateString(),
            'estado_asignacion' => 'activo',
        ]);

        return Pago::create([
            'asignacion_id' => $asignacion->id,
            'monto' => 100,
            'fecha' => now()->toDateString(),
            'estado_pago' => 'pendiente',
            'tipo_pago' => 'divisas',
        ]);
    }
}
