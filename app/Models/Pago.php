<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAPropietario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperPago
 */
class Pago extends Model
{
    use HasFactory, PerteneceAPropietario, SoftDeletes;

    // Un pago asociado a un alquiler: monto, fecha, estado
    // (pendiente/confirmado/rechazado, según el flujo de PagoController) y la
    // ruta al comprobante que el inquilino sube opcionalmente. confirmado_por
    // / confirmado_en registran quién y cuándo lo confirmó (ver PagoController).
    protected $fillable = [
        'asignacion_id',
        'monto',
        'fecha',
        'estado_pago',
        'tipo_pago',
        'ruta_comprobante',
        'confirmado_por',
        'confirmado_en',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'confirmado_en' => 'datetime',
    ];

    // Monedas posibles de un pago y su símbolo. No hay columna 'moneda': se
    // deriva 1:1 de tipo_pago (ver monedaDeTipo) para que no pueda quedar
    // desincronizada de él.
    public const MONEDAS = [
        'USD' => '$',
        'VES' => 'Bs.',
    ];

    // divisas → USD, pago_movil → VES. Un tipo_pago null (el pago pendiente
    // que se genera al registrar un inquilino) se toma como USD porque su
    // monto es el precio del alquiler, que se maneja en dólares.
    public static function monedaDeTipo(?string $tipoPago): string
    {
        return $tipoPago === 'pago_movil' ? 'VES' : 'USD';
    }

    // Formatea un monto con el símbolo de su moneda: "$100.00" / "Bs. 100.00".
    public static function formatearMonto(float|string|null $monto, string $moneda): string
    {
        $simbolo = self::MONEDAS[$moneda] ?? $moneda;
        $separador = $moneda === 'USD' ? '' : ' ';

        return $simbolo . $separador . number_format((float) $monto, 2);
    }

    // Accessor $pago->moneda ('USD' / 'VES').
    public function getMonedaAttribute(): string
    {
        return self::monedaDeTipo($this->tipo_pago);
    }

    // Accessor $pago->monto_formateado, listo para pintar en las vistas.
    public function getMontoFormateadoAttribute(): string
    {
        return self::formatearMonto($this->monto, $this->moneda);
    }

    // confirma: Pago N --- 1 Usuario (el propietario/encargado que confirmó el pago)
    public function confirmadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'confirmado_por');
    }

    // genera: Pago N --- 1 Asignacion (cada pago pertenece a un único alquiler)
    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(Asignacion::class);
    }

    // Ownership heredado de la asignación (alquiler) a la que pertenece.
    protected function filtrarPorPropietario(Builder $query, int $propietarioId): Builder
    {
        return $query->whereHas('asignacion', fn ($q) => $q->dePropietario($propietarioId));
    }
}
