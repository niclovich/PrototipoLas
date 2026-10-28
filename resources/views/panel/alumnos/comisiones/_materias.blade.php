{{-- Tabla de materias de la comisión elegida (CU3 P3 / Cambiar P3): Materia · Codigo · Carga Hs. · Estado. --}}
<h3 class="las-subtitulo text-muted font-weight-normal">{{ $titulo }}</h3>
<div class="table-responsive">
    <table class="table mb-4">
        <thead class="las-thead">
            <tr>
                <th scope="col">Materia</th>
                <th scope="col">Codigo</th>
                <th scope="col">Carga Hs.</th>
                <th scope="col">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($materias ?? [] as $materia)
                <tr>
                    <td>{{ $materia['det']->detallePlan->materia->nombre_materia }}</td>
                    <td>{{ $materia['det']->detallePlan->codigo_det_plan_estudio }}</td>
                    <td>{{ $materia['det']->detallePlan->carga_horaria_det_plan_estudio }} hs</td>
                    <td @class(['text-muted' => ! $materia['habilitada']])>{{ $materia['estado'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">Seleccione una comisión para ver sus materias.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
