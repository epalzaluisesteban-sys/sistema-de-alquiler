<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAPropietario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperPago
 */
class Pago extends Model
{
    use HasFactory, PerteneceAPropietario;

    // Un pago asociado a un alquiler: monto, fecha, estado
    // (pendiente/confirmado/rechazado, según el flujo de PagoController) y la
    // ruta al comprobante que el inquilino sube opcionalmente.
    protected $fillable = [
        'asignacion_id',
        'monto',
        'fecha',
        'estado_pago',
        'tipo_pago',
        'ruta_comprobante',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

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
