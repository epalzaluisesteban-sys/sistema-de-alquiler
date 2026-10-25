<?php

namespace App\Models\Concerns;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;

/**
 * Scope de ownership compartido por los modelos del panel admin: limita una
 * consulta a los registros del Propietario dueño del usuario autenticado
 * (el propio Propietario, o el Propietario al que pertenece el Encargado).
 * Cada modelo define en filtrarPorPropietario() cómo llega desde sí mismo
 * hasta propiedades.usuario_id.
 */
trait PerteneceAPropietario
{
    // Model::porPropietario(): registros del dueño del usuario autenticado
    // (o del $usuario indicado). Si no se puede resolver un dueño (p. ej.
    // un encargado sin propietario asignado) no devuelve nada, nunca todo.
    public function scopePorPropietario(Builder $query, ?Usuario $usuario = null): Builder
    {
        $propietarioId = ($usuario ?? auth()->user())?->propietarioId();

        if ($propietarioId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $this->filtrarPorPropietario($query, $propietarioId);
    }

    // Model::dePropietario($id): igual que porPropietario() pero con el id
    // del dueño ya resuelto; es la que se usa dentro de los whereHas()
    // anidados entre modelos.
    public function scopeDePropietario(Builder $query, int $propietarioId): Builder
    {
        return $this->filtrarPorPropietario($query, $propietarioId);
    }

    abstract protected function filtrarPorPropietario(Builder $query, int $propietarioId): Builder;
}
