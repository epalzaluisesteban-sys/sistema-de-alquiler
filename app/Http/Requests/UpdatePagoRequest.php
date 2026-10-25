<?php

namespace App\Http\Requests;

use App\Models\Pago;

/**
 * Misma validación que el alta (StorePagoRequest), pero además autoriza
 * contra PagoPolicy::update antes de validar: así un propietario ajeno
 * recibe 403 en vez de errores de validación sobre un pago que no es suyo.
 */
class UpdatePagoRequest extends StorePagoRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->pago());
    }

    // Pago de la ruta /pagos/{id}, resuelto una sola vez para authorize()
    // y para el controlador.
    public function pago(): Pago
    {
        return once(fn () => Pago::findOrFail($this->route('id')));
    }
}
