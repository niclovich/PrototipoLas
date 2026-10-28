{{--
    P1 Gestión de Plan de Estudio (entrada de CU 5, 6, 7 y 8). Según la carrera elegida:
    P1c sin plan activo (Registrar_Plan_P1c.png) o P1 con plan (Registrar_Materia_P1.png = Correlatividades_P1.png).
    Variables: $carreras [id => nombre], $carrera, $plan, $planes [id => año], $puedeRegistrarPlan.
--}}
@extends('layouts.panel')

@section('title', 'Gestión de Plan de Estudio')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE PLAN DE ESTUDIO" />
@endsection

@section('contenido')
    <div class="card card-las-plano">
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6 text-center form-group">
                    <label for="filtro-carrera" class="text-muted">Seleccionar Carrera</label>
                    <select id="filtro-carrera" name="carrera" class="form-control las-select-bordo" data-plan-carrera
                            data-url="{{ route('panel.planes.index') }}">
                        @foreach ($carreras as $id => $nombre)
                            <option value="{{ $id }}" @selected($carrera?->id === $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($plan)
                @include('panel.academico.planes._con-plan')
            @else
                @include('panel.academico.planes._sin-plan')
            @endif
        </div>
    </div>
@endsection
