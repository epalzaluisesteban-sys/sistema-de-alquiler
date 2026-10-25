<?php

namespace Tests\Feature;

use App\Models\Asignacion;
use App\Models\Pago;
use App\Models\Propiedad;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardIngresosTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingresos_del_mes_se_separan_por_moneda(): void
    {
        $propietario = Usuario::create(['name' => 'Dueño Uno', 'cedula' => 'V-10000001', 'password' => 'secret123', 'rol' => 'propietario']);
        $otro = Usuario::create(['name' => 'Dueño Dos', 'cedula' => 'V-20000002', 'password' => 'secret123', 'rol' => 'propietario']);

        $asignacion = $this->crearAsignacion($propietario, 'V-30000003');
        $ajena = $this->crearAsignacion($otro, 'V-40000004');

        $hoy = now()->toDateString();
        $this->crearPago($asignacion, 100, 'divisas', 'confirmado', $hoy);
        $this->crearPago($asignacion, 50, null, 'confirmado', $hoy);            // pago auto-generado: USD
        $this->crearPago($asignacion, 3600, 'pago_movil', 'confirmado', $hoy);
        $this->crearPago($asignacion, 999, 'divisas', 'pendiente', $hoy);       // no confirmado
        $this->crearPago($asignacion, 999, 'divisas', 'confirmado', now()->subMonths(2)->toDateString()); // otro mes
        $this->crearPago($ajena, 999, 'divisas', 'confirmado', $hoy);           // otro propietario

        $this->actingAs($propietario)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('ingresosMes', ['USD' => 150.0, 'VES' => 3600.0])
            ->assertSee('$150.00')
            ->assertSee('Bs. 3,600.00');
    }

    public function test_monto_formateado_usa_la_moneda_del_tipo_de_pago(): void
    {
        $this->assertSame('$10.50', (new Pago(['monto' => 10.5, 'tipo_pago' => 'divisas']))->monto_formateado);
        $this->assertSame('Bs. 10.50', (new Pago(['monto' => 10.5, 'tipo_pago' => 'pago_movil']))->monto_formateado);
        $this->assertSame('$10.50', (new Pago(['monto' => 10.5]))->monto_formateado);
    }

    private function crearAsignacion(Usuario $propietario, string $cedulaInquilino): Asignacion
    {
        $inquilino = Usuario::create(['name' => 'Inqui Lino', 'cedula' => $cedulaInquilino, 'password' => 'secret123', 'rol' => 'inquilino']);

        $propiedad = Propiedad::create([
            'usuario_id' => $propietario->id,
            'nombre' => 'Casa ' . $cedulaInquilino,
            'estado' => 'ocupada',
            'tipo' => 'completa',
        ]);

        return Asignacion::create([
            'usuario_id' => $inquilino->id,
            'propiedad_id' => $propiedad->id,
            'fecha_inicio' => now()->subYear()->toDateString(),
            'estado_asignacion' => 'activo',
        ]);
    }

    private function crearPago(Asignacion $asignacion, float $monto, ?string $tipo, string $estado, string $fecha): void
    {
        Pago::create([
            'asignacion_id' => $asignacion->id,
            'monto' => $monto,
            'tipo_pago' => $tipo,
            'estado_pago' => $estado,
            'fecha' => $fecha,
        ]);
    }
}
