<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperPago
 */
class Pago extends Model
{
    use HasFactory;

    // Un pago asociado a un alquiler: monto, fecha, estado
    // (pendiente/confirmado/rechazado, según el flujo de PagoController) y la
    // ruta al comprobante que el inquilino sube opcionalmente.
    protected $fillable = [
        'asignacion_id',
        'monto',
        'fecha',
        'estado_pago',
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
}