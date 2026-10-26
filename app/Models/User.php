<?php

namespace App\Models;

use App\Enums\EstadoCivil;
use App\Enums\EstadoCuenta;
use App\Enums\RolSistema;
use App\Enums\Sexo;
use App\Models\Concerns\AuditaCambios;
use App\Notifications\RestablecerContrasenia;
use App\Notifications\VerificarCorreo;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Clase Usuario de la tesis (Hoja 1). Se accede con correo (email) y contraseña;
 * dni_usuario es único pero no sirve para entrar. estado_cuenta controla el acceso.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use AuditaCambios, HasFactory, HasRoles, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id';

    protected $fillable = [
        'dni_usuario',
        'apellido_usuario',
        'nombre_usuario',
        'fecha_nacimiento',
        'sexo',
        'estado_civil',
        'telefono_usuario',
        'celular_usuario',
        'email',
        'direccion_usuario',
        'barrio_usuario',
        'pais_usuario',
        'localidad_id',
        'foto_usuario',
    ];

    // password, fecha_alta y estado_cuenta no son asignables en masa: se fijan con forceFill en UsuarioService/AdminSeeder.

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_nacimiento' => 'date',
            'fecha_alta' => 'date',
            'estado_cuenta' => EstadoCuenta::class,
            'sexo' => Sexo::class,
            'estado_civil' => EstadoCivil::class,
        ];
    }

    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn () => trim("{$this->apellido_usuario}, {$this->nombre_usuario}", ', '));
    }

    /** Alias que usa AdminLTE en el menú de usuario. */
    protected function name(): Attribute
    {
        return Attribute::get(fn () => trim("{$this->nombre_usuario} {$this->apellido_usuario}"));
    }

    protected function email(): Attribute
    {
        return Attribute::set(fn (string $valor) => mb_strtolower(trim($valor)));
    }

    public function estaActivo(): bool
    {
        return $this->estado_cuenta === EstadoCuenta::Activo;
    }

    public function tieneRol(RolSistema $rol): bool
    {
        return $this->hasRole($rol->value);
    }

    /** Un rol por usuario (plan §2.1): nombre del primero, o null. */
    public function nombreRol(): ?string
    {
        return $this->roles->first()?->name;
    }

    public function scopeConEstadoCuenta(Builder $query, EstadoCuenta $estado): Builder
    {
        return $query->where('estado_cuenta', $estado->value);
    }

    public function localidad(): BelongsTo
    {
        return $this->belongsTo(Localidad::class)->withTrashed();
    }

    public function encuesta(): HasOne
    {
        return $this->hasOne(EncuestaSocioeconomica::class);
    }

    /** Un Alumno (legajo) por carrera. */
    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoPresentado::class);
    }

    public function novedades(): HasMany
    {
        return $this->hasMany(Novedad::class);
    }

    /** Cajas abiertas por el usuario (rol Cajero). */
    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class);
    }

    /** Participación en tribunales (rol Docente). */
    public function integracionesTribunal(): HasMany
    {
        return $this->hasMany(IntegranteTribunal::class);
    }

    /** Subclases de Usuario de la Hoja 1 = roles (plan §4). */
    public function scopeConRol(Builder $query, RolSistema $rol): Builder
    {
        return $query->role($rol->value);
    }

    public function scopeDocentes(Builder $query): Builder
    {
        return $query->conRol(RolSistema::Docente);
    }

    public function scopeCajeros(Builder $query): Builder
    {
        return $query->conRol(RolSistema::Cajero);
    }

    /** Usuarios que se gestionan desde Administración → Usuarios (los Alumno se gestionan en CU1 / CU 9). */
    public function scopeGestionablesDesdeUsuarios(Builder $query): Builder
    {
        return $query->whereDoesntHave('roles', fn (Builder $roles) => $roles->where('name', RolSistema::Alumno->value));
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new RestablecerContrasenia($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerificarCorreo);
    }

    /** Texto bajo el nombre en el menú de usuario de AdminLTE. */
    public function adminlte_desc(): string
    {
        return $this->nombreRol() ?? '';
    }
}
