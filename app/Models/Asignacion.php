<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asignacion extends Model
{
    use HasFactory;

    protected $table = 'asignaciones';

    protected $fillable = [
        'usuario_id',
        'habitacion_id',
        'propiedad_id',
        'fecha_inicio',
        'fecha_fin',
        'estado_asignacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    // ocupa: Asignacion N --- 1 Usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    // asignada: Asignacion N --- 1 Habitacion (alquiler de una habitación puntual)
    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class);
    }

    // asignada: Asignacion N --- 1 Propiedad (alquiler de la propiedad completa, tipo "Casa")
    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    // Propiedad efectiva de la asignación, venga de una habitación o de la propiedad completa.
    public function residencia(): ?Propiedad
    {
        return $this->habitacion?->propiedad ?? $this->propiedad;
    }

    // genera: Asignacion 1 --- N Pago
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    // genera: Asignacion 1 --- N Reporte
    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class);
    }
}