<?php

namespace App\DataTables;

use App\Enums\EstadoGeneral;
use App\Support\DefinicionCatalogo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

/** DataTable del catálogo de la ruta: Activos = sin baja, Eliminados = con SoftDelete. */
class CatalogoDataTable extends BaseDataTable
{
    private DefinicionCatalogo $catalogo;

    protected function consulta(Request $request): Builder
    {
        $this->catalogo = $request->route('catalogo');
        $consulta = $this->catalogo->consulta();

        return $this->pestania($request) === self::PESTANIA_ELIMINADOS ? $consulta->onlyTrashed() : $consulta;
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        foreach ($this->catalogo->campos as $campo => $def) {
            if ($this->catalogo->tipo($campo) === 'select') {
                $tabla->addColumn("{$campo}_texto", fn (Model $fila) => $fila->{$def['relacion']}?->{$def['texto']} ?? '—');
            }
        }

        return $tabla
            ->addColumn('estado', fn (Model $fila) => $this->badge($fila->trashed() ? EstadoGeneral::Inactivo : EstadoGeneral::Activo));
    }

    protected function campos(): array
    {
        return [
            'id',
            ...collect($this->catalogo->campos)->keys()->map(fn (string $campo) => $this->catalogo->tipo($campo) === 'select' ? "{$campo}_texto" : $campo),
        ];
    }

    protected function vistaAcciones(): string
    {
        return 'panel.administracion.catalogos._acciones';
    }

    protected function datosAcciones(Model $fila): array
    {
        return ['catalogo' => $this->catalogo];
    }
}
