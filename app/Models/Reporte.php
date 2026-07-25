<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperReporte
 */
class Reporte extends Model
{
    use HasFactory;

    // Un reporte de mantenimiento: a qué alquiler pertenece, qué problema
    // describe el inquilino, cuándo se creó y en qué estado está
    // (p. ej. pendiente/en revisión/resuelto).
    protected $fillable = [
        'asignacion_id',
        'descripcion',
        'fecha',
        'estado_reporte',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // genera: Reporte N --- 1 Asignacion (cada reporte pertenece a un único alquiler)
    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(Asignacion::class);
    }
}