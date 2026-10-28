<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

/**
 * Clase Rol de la tesis (Hoja 1) implementada sobre la tabla roles de Spatie.
 * nombreRol = name, descripcionRol = descripcion, estadoRol = estado.
 */
class Rol extends Role
{
    use AuditaCambios;

    protected $table = 'roles';

    protected $primaryKey = 'id';

    protected $fillable = ['name', 'guard_name', 'descripcion', 'estado'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoGeneral::class,
        ];
    }

    /** Los roles de la tesis (Enum RolSistema) no se renombran ni se dan de baja. */
    public function esDelSistema(): bool
    {
        return RolSistema::tryFrom($this->name) !== null;
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', EstadoGeneral::Activo->value);
    }

    /** Roles activos de los 6 actores del personal (CU 25/26). */
    public function scopeDelPersonal(Builder $query): Builder
    {
        return $query->activos()->whereIn('name', RolSistema::valoresDelPersonal());
    }
}
