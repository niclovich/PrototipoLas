{{-- P1c (Registrar_Plan_P1c.png): la carrera no tiene un Plan de Estudio activo. Entrada de CU 5. --}}
<div class="text-center las-plan-vacio">
    <i class="fas fa-clipboard-list fa-4x las-plan-vacio__icono mb-3" aria-hidden="true"></i>
    <p class="text-muted font-italic mb-3">Esta carrera no tiene un plan de estudio registrado</p>
    @if ($carrera)
        @if ($puedeRegistrarPlan && auth()->user()->can('crear_plan_estudio'))
            <a href="{{ route('panel.planes.create', $carrera) }}" class="btn btn-las las-btn-principal px-5">Registrar Plan de Estudio</a>
        @else
            <span class="d-inline-block" tabindex="0" data-toggle="tooltip"
                  title="{{ $puedeRegistrarPlan ? 'No tiene permiso para esta acción' : 'La carrera no admite un nuevo plan de estudio' }}">
                <button type="button" class="btn btn-las las-btn-principal px-5 las-btn-deshabilitado" disabled>Registrar Plan de Estudio</button>
            </span>
        @endif
    @endif
</div>
