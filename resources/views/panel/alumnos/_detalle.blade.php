{{--
    Fila expandida de P1 Gestión de Alumnos: un renglón por Alumno (legajo por carrera).
    - Activos (Fig. 72): Legajo · Carrera · Estado · Comision (CU3) · Examen (CU5) · Acciones (👁, $, ⋮).
    - Preinscriptos (Fig. 68): Legajo · Carrera · Estado · Acciones (👁, $, "Inscribir →" de CU2, ⋮).
    👁 y ⋮ están fuera del alcance del prototipo.
--}}
@php
    $fueraDeAlcance = 'Fuera del alcance del prototipo';
@endphp
<div class="table-responsive">
    <table class="table table-sm mb-0 las-detalle-alumno">
        <thead class="las-thead">
            <tr>
                <th scope="col">Legajo</th>
                <th scope="col">Carrera</th>
                <th scope="col" class="text-center">Estado</th>
                @unless ($preinscriptos)
                    <th scope="col" class="text-center">Comision</th>
                    <th scope="col" class="text-center">Examen</th>
                @endunless
                <th scope="col" class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($alumnos as $alumno)
                <tr>
                    <td>{{ $alumno->nro_legajo ?? '-' }}</td>
                    <td>{{ $alumno->carrera?->nombre_carrera }}</td>
                    <td class="text-center"><x-badge-estado :estado="$alumno->estado_alumno" /></td>
                    @unless ($preinscriptos)
                        <td class="text-center">
                            @can('crear_inscripcion_comision')
                                <x-btn-accion :href="route('panel.alumno-comisiones.index', $alumno)" icono="las-letra-c" texto="Comision" color="las" />
                            @else
                                <x-btn-accion icono="las-letra-c" texto="Comision" color="las" deshabilitado="No tiene permiso para inscribir en comisiones" />
                            @endcan
                        </td>
                        <td class="text-center">
                            @can('crear_inscripcion_examen')
                                <x-btn-accion :href="route('panel.inscripciones-examen.create', $alumno)" icono="fas fa-file-signature" texto="Examen" color="las" />
                            @else
                                <x-btn-accion icono="fas fa-file-signature" texto="Examen" color="las" deshabilitado="No tiene permiso para inscribir a exámenes" />
                            @endcan
                        </td>
                    @endunless
                    <td class="text-center text-nowrap">
                        <x-btn-accion icono="fas fa-eye" texto="Ver" color="las" :deshabilitado="$fueraDeAlcance" />
                        <x-btn-accion :href="route('panel.pagos.create', $alumno)" icono="fas fa-dollar-sign" texto="Registrar Pago de Cuotas" color="las" permiso="crear_pago" />
                        @if ($preinscriptos)
                            @can('crear_inscripcion')
                                <a href="{{ route('panel.inscripciones.create', $alumno) }}" class="btn btn-sm btn-las px-3">Inscribir &rarr;</a>
                            @else
                                <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="No tiene permiso para inscribir alumnos">
                                    <button type="button" class="btn btn-sm btn-las px-3 las-btn-deshabilitado" disabled>Inscribir &rarr;</button>
                                </span>
                            @endcan
                        @endif
                        <x-btn-accion icono="fas fa-ellipsis-v" texto="Más opciones" color="las" :deshabilitado="$fueraDeAlcance" />
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
