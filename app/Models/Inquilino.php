<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperInquilino
 */
class Inquilino extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
    ];

    // extiende: Inquilino 1 --- 1 Usuario (la cuenta de login del inquilino)
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    // ocupa: delega a Usuario->asignaciones() en vez de duplicar la FK, ya
    // que 'asignaciones.usuario_id' sigue apuntando a 'usuarios', no a esta tabla.
    public function asignaciones(): HasMany
    {
        return $this->usuario->asignaciones();
    }
}
