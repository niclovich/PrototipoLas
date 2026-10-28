<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Búsqueda de los filtros "Buscar.." de las pantallas: palabra por palabra (todas deben aparecer: AND),
 * cada una en alguna de las columnas o relaciones indicadas (OR). Ignora la coma, escapa % _ \ y permite
 * descartar palabras de relleno (p. ej. "Resolución", "N°").
 */
trait BuscaPorPalabras
{
    /**
     * @param  list<string>  $columnas  columnas propias
     * @param  array<string, list<string>>  $relaciones  relación => columnas de esa relación
     * @param  list<string>  $ignorar  palabras que no filtran (se comparan en minúsculas)
     */
    public function scopeBuscarPalabras(Builder $query, ?string $texto, array $columnas, array $relaciones = [], array $ignorar = []): Builder
    {
        $ignorar = array_map(fn (string $palabra) => mb_strtolower($palabra), $ignorar);
        $palabras = array_filter(
            preg_split('/[\s,]+/u', trim((string) $texto), -1, PREG_SPLIT_NO_EMPTY),
            fn (string $palabra) => ! in_array(mb_strtolower($palabra), $ignorar, true),
        );

        foreach ($palabras as $palabra) {
            $patron = '%'.addcslashes($palabra, '\\%_').'%';

            $query->where(function (Builder $q) use ($columnas, $relaciones, $patron) {
                foreach ($columnas as $columna) {
                    $q->orWhere($q->qualifyColumn($columna), 'like', $patron);
                }
                foreach ($relaciones as $relacion => $columnasRelacion) {
                    $q->orWhereHas($relacion, fn (Builder $r) => $r->where(function (Builder $w) use ($columnasRelacion, $patron) {
                        foreach ($columnasRelacion as $columna) {
                            $w->orWhere($columna, 'like', $patron);
                        }
                    }));
                }
            });
        }

        return $query;
    }
}
