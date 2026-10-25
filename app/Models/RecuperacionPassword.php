<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecuperacionPassword extends Model
{
    protected $table = 'recuperaciones_password';

    protected $fillable = [
        'usuario_id',
        'token',
        'expira_en',
    ];

    protected $casts = [
        'expira_en' => 'datetime',
    ];

    // pertenece: RecuperacionPassword N --- 1 Usuario (el dueño de la cuenta a recuperar)
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function haExpirado(): bool
    {
        return $this->expira_en->isPast();
    }
}
