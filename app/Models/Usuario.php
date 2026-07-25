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

    // Modelo de autenticación real del sistema (ver config/auth.php): no se
    // usa la tabla 'users' de Laravel, sino 'usuarios'.
    protected $table = 'usuarios';

    // Campos asignables en masa. Incluye tanto las columnas reales
    // ('cedula', 'nombre', 'apellido', 'telefono', 'contrasena', 'rol') como
    // los "virtuales" ('name', 'password', 'role') que pasan por los
    // accessors/mutators de abajo antes de guardarse.
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

    // Oculta la contraseña hasheada al serializar el modelo (por ejemplo en respuestas JSON).
    protected $hidden = [
        'contrasena',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Le dice a Laravel que el login se hace por 'cedula', no por 'email'.
    public function getAuthIdentifierName(): string
    {
        return 'cedula';
    }

    // Laravel llama esto internamente para comparar la contraseña en
    // Auth::attempt(); apunta a la columna real 'contrasena'.
    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    // Accessor: permite leer $usuario->password devolviendo el hash guardado en 'contrasena'.
    public function getPasswordAttribute(): ?string
    {
        return $this->contrasena;
    }

    // Mutator: al asignar 'password' => $valor, hashea con bcrypt antes de
    // guardar en 'contrasena'. Si el valor ya viene hasheado (prefijo $2y$ y
    // 60 caracteres, p. ej. al restaurar un seeder) lo deja tal cual para no
    // hashearlo dos veces.
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

    // Accessor: traduce la columna cruda 'rol' (propietario/encargado/inquilino)
    // al valor que usa el resto de la app (admin/encargado/tenant) para
    // middleware y redirecciones.
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

    // Mutator inverso: si se asigna 'role' => 'admin'/'tenant', lo convierte
    // de vuelta al valor real que acepta la columna 'rol' en la BD.
    public function setRoleAttribute($value): void
    {
        $role = strtolower(trim((string) $value));

        $this->attributes['rol'] = match ($role) {
            'admin', 'propietario' => 'propietario',
            'tenant', 'inquilino' => 'inquilino',
            default => $role,
        };
    }

    // Accessor: arma el nombre completo uniendo 'nombre' + 'apellido' para
    // exponerlo como un solo campo 'name' (usado en formularios).
    public function getNameAttribute(): string
    {
        $nombre = trim($this->attributes['nombre'] ?? '');
        $apellido = trim($this->attributes['apellido'] ?? '');

        if ($nombre === '' && $apellido === '') {
            return '';
        }

        return trim($nombre . ' ' . $apellido);
    }

    // Mutator: separa un 'name' recibido de un formulario en 'nombre'
    // (primera palabra) y 'apellido' (resto), para guardarlos por separado.
    public function setNameAttribute($value): void
    {
        $parts = preg_split('/\s+/', trim((string) $value), 2, PREG_SPLIT_NO_EMPTY);
        $this->attributes['nombre'] = $parts[0] ?? '';
        $this->attributes['apellido'] = $parts[1] ?? '';
    }

    // registra: Usuario 1 --- N Propiedad (propiedades que este usuario posee como dueño)
    public function propiedades(): HasMany
    {
        return $this->hasMany(Propiedad::class);
    }

    // ocupa: Usuario 1 --- N Asignacion (alquileres en los que este usuario es el inquilino)
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class);
    }

    // Helpers de rol basados en la columna cruda 'rol' (no en el accessor 'role').
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