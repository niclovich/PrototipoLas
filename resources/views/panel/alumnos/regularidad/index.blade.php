{{-- CU4 P1 Gestión de Comisiones (CU4_regularidad/wireframes/P1.png, Fig. 76): pasos 2–7. --}}
@extends('layouts.panel')

@section('title', 'Regularidad Académica')

@section('encabezado')
    <x-header-with-title titulo="GESTION DE COMISIONES" />
@endsection

@section('contenido')
    <form method="GET" action="{{ route('panel.regularidad.index') }}" class="las-combo-centrado text-center mb-3">
        <label for="carrera_id" class="mb-1">Seleccionar Carrera</label>
        <select name="carrera_id" id="carrera_id" class="form-control" data-autoenviar>
            @forelse ($carreras as $id => $nombre)
                <option value="{{ $id }}" @selected($carrera?->id === $id)>{{ $nombre }}</option>
            @empty
                <option value="">No hay carreras en estado Activo</option>
            @endforelse
        </select>
        <noscript><button type="submit" class="btn btn-las mt-2">Ver comisiones</button></noscript>
    </form>

    <div class="d-flex justify-content-end mb-2">
        {{-- "Registrar Comision" es el CU 17 (Coordinador). --}}
        @if ($carrera && auth()->user()->can('crear_comision'))
            <a href="{{ route('panel.comisiones.create', $carrera) }}" class="btn btn-las las-btn-principal">Registrar Comision</a>
        @else
            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="Registrar Comisión (CU 17) corresponde al Coordinador">
                <button type="button" class="btn btn-las las-btn-principal las-btn-deshabilitado" disabled>Registrar Comision</button>
            </span>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead class="las-thead">
                <tr>
                    <th scope="col">Codigo</th>
                    <th scope="col">Anio Curso</th>
                    <th scope="col">Anio Lectivo</th>
                    <th scope="col">Horario</th>
                    <th scope="col">Modalidad</th>
                    <th scope="col">Accion</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comisiones as $comision)
                    <tr>
                        <td>{{ $comision->codigo_comision }}</td>
                        <td>{{ $comision->anioCursoTexto() }}</td>
                        <td>{{ $comision->anio_lectivo }}</td>
                        <td>{{ $comision->horario_comision }}</td>
                        <td>{{ $comision->modalidad?->modalidad }}</td>
                        <td class="text-nowrap">
                            <x-btn-accion :href="route('panel.regularidad.materias', $comision)" icono="fas fa-eye" texto="Ver materias" color="las" />
                            <x-btn-accion icono="fas fa-trash-alt" texto="Eliminar" color="las" deshabilitado="Fuera del alcance del prototipo" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">La carrera no tiene comisiones en estado Activo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
