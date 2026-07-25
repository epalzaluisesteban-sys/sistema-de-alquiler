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

    // Datos de una habitación dentro de una propiedad: a qué propiedad
    // pertenece, su número/identificador, precio de alquiler y estado
    // (Disponible/Ocupada/Mantenimiento).
    protected $fillable = [
        'propiedad_id',
        'numero',
        'precio',
        'estado',
    ];

    // contiene: Habitacion N --- 1 Propiedad (la propiedad a la que pertenece esta habitación)
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    // asignada: Habitacion 1 --- N Asignacion (historial de alquileres de esta habitación)
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }
}