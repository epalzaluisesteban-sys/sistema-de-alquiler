<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Propiedad extends Model
{
    use HasFactory;

    protected $table = 'propiedades';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'descripcion',
        'estado',
    ];

    // registra: Propiedad N --- 1 Usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    // contiene: Propiedad 1 --- N Habitacion
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