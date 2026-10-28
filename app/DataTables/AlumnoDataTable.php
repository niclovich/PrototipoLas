<?php

namespace App\DataTables;

use App\Enums\EstadoAlumno;
use App\Enums\RolSistema;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Yajra\DataTables\EloquentDataTable;

/**
 * P1 Gestión de Alumnos (Fig. 72): una fila por Usuario (NOMBRE · DNI · EMAIL) y, al expandirla, una fila por
 * Alumno (legajo por carrera) de la pestaña: Activos (no Preinscripto) o Preinscriptos. El Alumno ve solo lo suyo.
 */
class AlumnoDataTable extends BaseDataTable
{
    public const PESTANIA_PREINSCRIPTOS = 'preinscriptos';

    private bool $preinscriptos = false;

    protected function consulta(Request $request): Builder
    {
        $preinscriptos = $this->preinscriptos = $request->input('pestania') === self::PESTANIA_PREINSCRIPTOS;
        $filtro = fn ($a) => $preinscriptos
            ? $a->where('estado_alumno', EstadoAlumno::Preinscripto->value)
            : $a->where('estado_alumno', '!=', EstadoAlumno::Preinscripto->value);

        return User::query()
            ->select('users.*')
            ->whereHas('alumnos', $filtro)
            ->with(['alumnos' => fn ($a) => $filtro($a)->with('carrera')->orderBy('id')])
            ->when($request->user()->tieneRol(RolSistema::Alumno), fn (Builder $q) => $q->whereKey($request->user()->id))
            ->buscar($request->input('buscar'))
            ->orderBy('apellido_usuario')
            ->orderBy('nombre_usuario');
    }

    protected function columnas(EloquentDataTable $tabla): EloquentDataTable
    {
        return $tabla
            ->addColumn('control', fn () => view('panel.alumnos._control')->render())
            ->addColumn('nombre', fn (User $u) => $u->nombre_completo)
            ->addColumn('estado', fn () => '');
    }

    protected function campos(): array
    {
        return ['id', 'control', 'nombre', 'dni_usuario', 'email'];
    }

    protected function columnasHtml(): array
    {
        return ['control'];
    }

    /** La "acción" de la fila es su detalle expandible: Activos (Fig. 72) o Preinscriptos (Fig. 68, con "Inscribir →"). */
    protected function vistaAcciones(): string
    {
        return 'panel.alumnos._detalle';
    }

    protected function datosAcciones(Model $fila): array
    {
        return ['alumnos' => $fila->alumnos, 'preinscriptos' => $this->preinscriptos];
    }
}
