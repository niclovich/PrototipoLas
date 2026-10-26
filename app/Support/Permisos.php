<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Lectura de config/permisos.php: permisos {accion}_{entidad} agrupados y con etiquetas en español.
 */
final class Permisos
{
    /**
     * @return array<string, array<string, string>> etiqueta de entidad => [permiso => etiqueta de acción]
     */
    public static function agrupados(): array
    {
        $acciones = config('permisos.acciones');
        $entidades = config('permisos.entidades');

        return collect(config('permisos.permisos'))
            ->groupBy(fn (string $permiso) => $entidades[Str::after($permiso, '_')] ?? Str::after($permiso, '_'))
            ->map(fn ($permisos) => $permisos
                ->mapWithKeys(fn (string $permiso) => [$permiso => $acciones[Str::before($permiso, '_')] ?? $permiso])
                ->all())
            ->all();
    }
}
