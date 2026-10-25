<?php

namespace Tests\Feature;

use App\Models\Asignacion;
use App\Models\Pago;
use App\Models\Propiedad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PagoComprobanteTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $propietario;
    private Pago $pago;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->propietario = Usuario::create(['name' => 'Dueño Uno', 'cedula' => 'V-10000001', 'password' => 'secret123', 'rol' => 'propietario']);
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
            'tipo_pago' => 'pago_movil',
            'ruta_comprobante' => UploadedFile::fake()->create('comprobante.pdf', 10, 'application/pdf')->store('comprobantes', 'public'),
        ]);
    }

    public function test_soft_delete_conserva_el_comprobante(): void
    {
        $this->actingAs($this->propietario)
            ->delete(route('admin.destroy-pago', $this->pago->id));

        $this->assertSoftDeleted('pagos', ['id' => $this->pago->id]);
        Storage::disk('public')->assertExists($this->pago->ruta_comprobante);
    }

    public function test_eliminar_definitivo_borra_el_comprobante(): void
    {
        $this->pago->delete();

        $this->actingAs($this->propietario)
            ->delete(route('admin.force-destroy-pago', $this->pago->id));

        $this->assertDatabaseMissing('pagos', ['id' => $this->pago->id]);
        Storage::disk('public')->assertMissing($this->pago->ruta_comprobante);
    }

    public function test_store_pago_valida_con_form_request(): void
    {
        $this->actingAs($this->propietario)
            ->post(route('admin.store-pago'), ['tipo_pago' => 'cheque'])
            ->assertSessionHasErrors(['asignacion_id', 'monto', 'fecha', 'estado_pago', 'tipo_pago']);
    }
}
