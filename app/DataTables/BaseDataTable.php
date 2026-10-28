<?php

namespace App\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;

/**
 * Base de los DataTables server-side (yajra) del patrón index:
 * pestañas Activos/Eliminados, badge de estado y columna de acciones.
 */
abstract class BaseDataTable
{
    public const PESTANIA_ACTIVOS = 'activos';

    public const PESTANIA_ELIMINADOS = 'eliminados';

    abstract protected function consulta(Request $request): Builder;

    abstract protected function columnas(EloquentDataTable $tabla): EloquentDataTable;

    /**
     * Campos que viajan al navegador (además de estado y acciones).
     *
     * @return list<string>
     */
    abstract protected function campos(): array;

    /** Vista parcial con los botones de cada fila (recibe $fila). */
    abstract protected function vistaAcciones(): string;

    /**
     * Columnas propias que traen HTML (además de estado y acciones).
     *
     * @return list<string>
     */
    protected function columnasHtml(): array
    {
        return [];
    }

    /** Variables extra para la vista de acciones. */
    /** @return array<string, mixed> */
    protected function datosAcciones(Model $fila): array
    {
        return [];
    }

    public function respuesta(Request $request): JsonResponse
    {
        $tabla = DataTables::eloquent($this->consulta($request))
            ->addColumn('acciones', fn (Model $fila) => view($this->vistaAcciones(), ['fila' => $fila, ...$this->datosAcciones($fila)])->render());

        return $this->columnas($tabla)
            ->only([...$this->campos(), 'estado', 'acciones'])
            ->rawColumns(['estado', 'acciones', ...$this->columnasHtml()])
            ->toJson();
    }

    protected function pestania(Request $request): string
    {
        return $request->input('pestania') === self::PESTANIA_ELIMINADOS
            ? self::PESTANIA_ELIMINADOS
            : self::PESTANIA_ACTIVOS;
    }

    protected function badge(mixed $estado): string
    {
        return Blade::render('<x-badge-estado :estado="$estado" />', ['estado' => $estado]);
    }
}
