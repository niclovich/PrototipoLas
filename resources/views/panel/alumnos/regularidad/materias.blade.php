{{-- CU4 P2 Materias de la Comisión (CU4_regularidad/wireframes/P2.png, Fig. 77): pasos 8–10, agrupadas por cuatrimestre. --}}
@extends('layouts.panel')

@section('title', 'Regularidad Académica')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\RegularidadController::ENCABEZADO" />
@endsection

@section('contenido')
    <div class="card las-tarjeta mb-4">
        <div class="card-body py-4">
            <strong>Comision: {{ $comision->codigo_comision }} · {{ $comision->anioCursoTexto() }} · {{ $comision->carrera->nombre_carrera }}</strong>
        </div>
    </div>

    @forelse ($grupos as $cuatrimestre => $detalles)
        <h3 class="las-subtitulo">{{ mb_strtoupper($cuatrimestre) }}</h3>
        <div class="table-responsive">
            <table class="table mb-4">
                <thead class="las-thead">
                    <tr>
                        <th scope="col">Materia</th>
                        <th scope="col">Codigo</th>
                        <th scope="col">Carga Hs.</th>
                        <th scope="col">Accion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detalles as $det)
                        <tr>
                            <td>{{ $det->detallePlan->materia->nombre_materia }}</td>
                            <td>{{ $det->detallePlan->codigo_det_plan_estudio }}</td>
                            <td>{{ $det->detallePlan->carga_horaria_det_plan_estudio }} hs</td>
                            <td><x-btn-accion :href="route('panel.regularidad.alumnos', $det)" icono="fas fa-eye" texto="Ver alumnos inscriptos" color="las" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <p class="text-muted">La comisión no tiene materias.</p>
    @endforelse

    <a href="{{ route('panel.regularidad.index', ['carrera_id' => $comision->carrera_id]) }}" class="btn btn-outline-las las-btn-principal mt-3">Volver</a>
@endsection
