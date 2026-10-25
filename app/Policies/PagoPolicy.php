<?php

namespace App\Policies;

use App\Models\Pago;
use App\Models\Usuario;

/**
 * Autorización por ownership sobre un pago puntual: un Propietario (o un
 * Encargado de ese Propietario) solo puede ver/editar/borrar pagos cuya
 * asignación cae en una propiedad o habitación de ese Propietario.
 * Laravel la descubre automáticamente por convención (Pago → PagoPolicy).
 */
class PagoPolicy
{
    public function view(Usuario $usuario, Pago $pago): bool
    {
        return $this->perteneceAlPropietario($usuario, $pago);
    }

    public function update(Usuario $usuario, Pago $pago): bool
    {
        return $this->perteneceAlPropietario($usuario, $pago);
    }

    public function delete(Usuario $usuario, Pago $pago): bool
    {
        return $this->perteneceAlPropietario($usuario, $pago);
    }

    private function perteneceAlPropietario(Usuario $usuario, Pago $pago): bool
    {
        $propietarioId = $usuario->propietarioId();

        return $propietarioId !== null
            && (int) $pago->asignacion?->residencia()?->usuario_id === $propietarioId;
    }
}
