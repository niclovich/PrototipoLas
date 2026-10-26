<?php

namespace App\Services\Catalogos;

use App\Support\DefinicionCatalogo;
use Illuminate\Database\Eloquent\Model;

/**
 * ABM genérico de catálogos (SUPERADMIN). La baja es lógica (SoftDeletes): los registros
 * dados de baja siguen referenciados por las tablas de dominio.
 */
class CatalogoService
{
    /** @param  array<string, mixed>  $datos */
    public function crear(DefinicionCatalogo $catalogo, array $datos): Model
    {
        return $catalogo->modelo::create($datos);
    }

    /** @param  array<string, mixed>  $datos */
    public function actualizar(Model $registro, array $datos): Model
    {
        $registro->update($datos);

        return $registro;
    }

    public function darDeBaja(Model $registro): void
    {
        $registro->delete();
    }

    public function restaurar(Model $registro): void
    {
        $registro->restore();
    }
}
