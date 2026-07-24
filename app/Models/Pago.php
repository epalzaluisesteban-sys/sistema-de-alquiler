<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'asignacion_id',
        'monto',
        'fecha',
        'estado_pago',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    // genera: Pago N --- 1 Asignacion
    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(Asignacion::class);
    }
}