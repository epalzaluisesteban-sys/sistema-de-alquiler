<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperHabitacion
 */
class Habitacion extends Model
{
    use HasFactory;

    protected $table = 'habitaciones';

    protected $fillable = [
        'propiedad_id',
        'numero',
        'precio',
        'estado',
    ];

    // contiene: Habitacion N --- 1 Propiedad
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    // asignada: Habitacion 1 --- N Asignacion
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }
}