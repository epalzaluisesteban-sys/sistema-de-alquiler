<?php

namespace App\Models;

use App\Models\Concerns\PerteneceAPropietario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperHabitacion
 */
class Habitacion extends Model
{
    use HasFactory, PerteneceAPropietario;

    protected $table = 'habitaciones';

    // Datos de una habitación dentro de una propiedad: a qué propiedad
    // pertenece, su número/identificador y estado
    // (Disponible/Ocupada/Mantenimiento). El precio no se guarda aquí
    // (ver getPrecioActualAttribute): siempre se lee en vivo desde
    // usuarios.precio_habitacion.
    protected $fillable = [
        'propiedad_id',
        'numero',
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

    // Precio vigente de la habitación: siempre el definido en Ajustes por
    // el propietario dueño de la propiedad, para que un cambio ahí se
    // refleje de inmediato en todas las habitaciones existentes.
    public function getPrecioActualAttribute(): ?float
    {
        return $this->propiedad?->usuario?->precio_habitacion;
    }

    // Ownership heredado de la propiedad que contiene la habitación.
    protected function filtrarPorPropietario(Builder $query, int $propietarioId): Builder
    {
        return $query->whereHas('propiedad', fn ($q) => $q->dePropietario($propietarioId));
    }
}
