<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperAsignacion
 */
class Asignacion extends Model
{
    use HasFactory;

    protected $table = 'asignaciones';

    // Una asignación es el "contrato" que vincula a un inquilino (usuario_id)
    // con lo que alquila: o una habitación puntual (habitacion_id) o una
    // propiedad completa (propiedad_id) — solo uno de los dos suele estar
    // presente según el tipo de alquiler. Incluye fechas de inicio/fin y el
    // estado del alquiler (activa/finalizada, etc.).
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

    // ocupa: Asignacion N --- 1 Usuario (el inquilino de este alquiler)
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

    // Resuelve la propiedad "efectiva" de la asignación sin que el resto del
    // código tenga que preguntar si es alquiler por habitación o por
    // propiedad completa: si viene de una habitación, sube hasta su
    // propiedad; si no, usa la propiedad asignada directamente.
    public function residencia(): ?Propiedad
    {
        return $this->habitacion?->propiedad ?? $this->propiedad;
    }

    // genera: Asignacion 1 --- N Pago (historial de pagos de este alquiler)
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    // genera: Asignacion 1 --- N Reporte (reportes de mantenimiento de este alquiler)
    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class);
    }
}