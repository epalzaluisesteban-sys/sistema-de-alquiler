<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporte extends Model
{
    use HasFactory;

    protected $fillable = [
        'asignacion_id',
        'descripcion',
        'fecha',
        'estado_reporte',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    // genera: Reporte N --- 1 Asignacion
    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(Asignacion::class);
    }
}