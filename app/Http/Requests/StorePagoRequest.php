<?php

namespace App\Http\Requests;

use App\Models\Asignacion;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación del alta manual de un pago desde el panel admin. El acceso por
 * rol ya lo garantiza el middleware 'role:admin,encargado' de la ruta.
 */
class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asignacion_id' => ['required', 'exists:asignaciones,id', $this->asignacionDelPropietario()],
            'monto' => ['required', 'numeric', 'min:0'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'estado_pago' => ['required', 'in:pendiente,notificado,confirmado'],
            'tipo_pago' => ['required', 'in:divisas,pago_movil'],
        ];
    }

    // La asignación elegida en el formulario debe ser de una propiedad del
    // propietario actual (evita registrar o mover un pago hacia el alquiler
    // de otro propietario manipulando el select).
    private function asignacionDelPropietario(): Closure
    {
        return function (string $attribute, $value, Closure $fail) {
            if (! Asignacion::porPropietario()->whereKey($value)->exists()) {
                $fail('La asignación seleccionada no es válida.');
            }
        };
    }
}
