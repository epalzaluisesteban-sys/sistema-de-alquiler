<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperPropiedad
 */
class Propiedad extends Model
{
    use HasFactory;

    // Nombre de tabla explícito: por convención Eloquent buscaría
    // "propiedads", así que hay que indicarlo a mano (ver nota de
    // CLAUDE.md sobre nombres de tabla en español).
    protected $table = 'propiedades';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'descripcion',
        'estado',
    ];

    // registra: Propiedad N --- 1 Usuario (el dueño/propietario de esta propiedad)
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    // contiene: Propiedad 1 --- N Habitacion (las habitaciones que se pueden alquilar por separado)
    public function habitaciones(): HasMany
    {
        return $this->hasMany(Habitacion::class);
    }

    // asignada directamente (alquiler de la propiedad completa, sin pasar por Habitacion)
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }
}