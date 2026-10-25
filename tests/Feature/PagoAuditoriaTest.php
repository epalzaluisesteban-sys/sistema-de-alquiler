<?php

namespace Tests\Feature;

use App\Models\Asignacion;
use App\Models\Pago;
use App\Models\Propiedad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoAuditoriaTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $propietario;
    private Usuario $encargado;
    private Pago $pago;

    protected function setUp(): void
    {
        parent::setUp();

        $this->propietario = Usuario::create(['name' => 'Dueño Uno', 'cedula' => 'V-10000001', 'password' => 'secret123', 'rol' => 'propietario']);
        $this->encargado = Usuario::create(['name' => 'Enca Rgado', 'cedula' => 'V-20000002', 'password' => 'secret123', 'rol' => 'encargado', 'propietario_id' => $this->propietario->id]);
        $inquilino = Usuario::create(['name' => 'Inqui Lino', 'cedula' => 'V-30000003', 'password' => 'secret123', 'rol' => 'inquilino']);

        $propiedad = Propiedad::create(['usuario_id' => $this->propietario->id, 'nombre' => 'Casa', 'estado' => 'ocupada', 'tipo' => 'completa']);
        $asignacion = Asignacion::create([
            'usuario_id' => $inquilino->id,
            'propiedad_id' => $propiedad->id,
            'fecha_inicio' => now()->subMonth()->toDateString(),
            'estado_asignacion' => 'activo',
        ]);

        $this->pago = Pago::create([
            'asignacion_id' => $asignacion->id,
            'monto' => 100,
            'fecha' => now()->toDateString(),
            'estado_pago' => 'notificado',
            'tipo_pago' => 'divisas',
        ]);
    }

    public function test_confirmar_pago_registra_quien_y_cuando(): void
    {
        $this->actingAs($this->encargado)
            ->patch(route('admin.pagos.quick-estado', $this->pago->id), ['estado_pago' => 'confirmado'])
            ->assertRedirect();

        $pago = $this->pago->fresh();
        $this->assertSame($this->encargado->id, $pago->confirmado_por);
        $this->assertNotNull($pago->confirmado_en);

        // Volver a pendiente limpia la auditoría de la confirmación.
        $this->patch(route('admin.pagos.quick-estado', $this->pago->id), ['estado_pago' => 'pendiente']);
        $this->assertNull($this->pago->fresh()->confirmado_por);
    }

    public function test_update_pago_a_confirmado_registra_auditoria(): void
    {
        $this->actingAs($this->propietario)
            ->put(route('admin.update-pago', $this->pago->id), [
                'asignacion_id' => $this->pago->asignacion_id,
                'monto' => 100,
                'fecha' => now()->toDateString(),
                'estado_pago' => 'confirmado',
                'tipo_pago' => 'divisas',
            ])
            ->assertRedirect(route('admin.pagos'));

        $this->assertSame($this->propietario->id, $this->pago->fresh()->confirmado_por);
    }

    public function test_eliminar_pago_es_soft_delete(): void
    {
        $this->actingAs($this->encargado)
            ->delete(route('admin.destroy-pago', $this->pago->id))
            ->assertRedirect(route('admin.pagos'));

        $this->assertSoftDeleted('pagos', ['id' => $this->pago->id]);
    }

    public function test_papelera_es_solo_del_propietario(): void
    {
        $this->pago->delete();

        $this->actingAs($this->encargado);
        $this->get(route('admin.pagos-papelera'))->assertForbidden();
        $this->patch(route('admin.restore-pago', $this->pago->id))->assertForbidden();
        $this->delete(route('admin.force-destroy-pago', $this->pago->id))->assertForbidden();

        $this->actingAs($this->propietario);
        $this->get(route('admin.pagos-papelera'))
            ->assertOk()
            ->assertViewHas('pagos', fn ($pagos) => $pagos->pluck('id')->all() === [$this->pago->id]);

        $this->patch(route('admin.restore-pago', $this->pago->id))->assertRedirect(route('admin.pagos-papelera'));
        $this->assertNotSoftDeleted('pagos', ['id' => $this->pago->id]);

        $this->pago->delete();
        $this->delete(route('admin.force-destroy-pago', $this->pago->id))->assertRedirect(route('admin.pagos-papelera'));
        $this->assertDatabaseMissing('pagos', ['id' => $this->pago->id]);
    }

    public function test_papelera_no_muestra_pagos_de_otro_propietario(): void
    {
        $this->pago->delete();
        $otro = Usuario::create(['name' => 'Dueño Dos', 'cedula' => 'V-40000004', 'password' => 'secret123', 'rol' => 'propietario']);

        $this->actingAs($otro);
        $this->get(route('admin.pagos-papelera'))
            ->assertOk()
            ->assertViewHas('pagos', fn ($pagos) => $pagos->isEmpty());
        $this->patch(route('admin.restore-pago', $this->pago->id))->assertForbidden();
    }
}
