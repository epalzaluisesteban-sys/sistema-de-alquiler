<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperUsuario
 */
class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'cedula',
        'nombre',
        'apellido',
        'telefono',
        'contrasena',
        'rol',
        'name',
        'password',
        'role',
    ];

    protected $hidden = [
        'contrasena',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getAuthIdentifierName(): string
    {
        return 'cedula';
    }

    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    public function getPasswordAttribute(): ?string
    {
        return $this->contrasena;
    }

    public function setPasswordAttribute($value): void
    {
        if (!empty($value)) {
            $value = (string) $value;
            if (str_starts_with($value, '$2y$') && strlen($value) === 60) {
                $this->attributes['contrasena'] = $value;
            } else {
                $this->attributes['contrasena'] = bcrypt($value);
            }
        }
    }

    public function getRoleAttribute(): ?string
    {
        $rol = strtolower($this->attributes['rol'] ?? $this->rol ?? '');

        return match ($rol) {
            'propietario' => 'admin',
            'encargado' => 'encargado',
            'inquilino' => 'tenant',
            default => $rol,
        };
    }

    public function setRoleAttribute($value): void
    {
        $role = strtolower(trim((string) $value));

        $this->attributes['rol'] = match ($role) {
            'admin', 'propietario' => 'propietario',
            'tenant', 'inquilino' => 'inquilino',
            default => $role,
        };
    }

    public function getNameAttribute(): string
    {
        $nombre = trim($this->attributes['nombre'] ?? '');
        $apellido = trim($this->attributes['apellido'] ?? '');

        if ($nombre === '' && $apellido === '') {
            return '';
        }

        return trim($nombre . ' ' . $apellido);
    }

    public function setNameAttribute($value): void
    {
        $parts = preg_split('/\s+/', trim((string) $value), 2, PREG_SPLIT_NO_EMPTY);
        $this->attributes['nombre'] = $parts[0] ?? '';
        $this->attributes['apellido'] = $parts[1] ?? '';
    }

    // registra: Usuario 1 --- N Propiedad
    public function propiedades(): HasMany
    {
        return $this->hasMany(Propiedad::class);
    }

    // ocupa: Usuario 1 --- N Asignacion
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }

    public function esAdministrador(): bool
    {
        return $this->rol === 'propietario';
    }

    public function esEncargado(): bool
    {
        return $this->rol === 'encargado';
    }

    public function esInquilino(): bool
    {
        return $this->rol === 'inquilino';
    }
}