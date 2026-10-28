{{--
    Encabezado de asistente: "PASO n DE N", porcentaje, pastillas y barra. Variables: $paso; opcional $pasos
    (por defecto los 5 de CU1; CU2 P2 usa "Buscar alumno · Datos y documentacion") y $etiqueta (aria-label).
--}}
@php
    $pasos ??= ['Buscar alumno', 'Datos personales', 'Encuesta', 'Carrera', 'Confirmar'];
    $total = count($pasos);
    $porcentaje = (int) round($paso * 100 / $total);
@endphp
<div class="las-asistente mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="las-asistente__paso">PASO {{ $paso }} DE {{ $total }}</span>
        <span class="las-asistente__porcentaje">{{ $porcentaje }}% completado</span>
    </div>
    <ol class="las-asistente__pastillas" aria-label="{{ $etiqueta ?? 'Pasos de la preinscripción' }}">
        @foreach ($pasos as $indice => $texto)
            @php $numero = $indice + 1; @endphp
            <li @class([
                    'las-asistente__pastilla',
                    'las-asistente__pastilla--hecha' => $numero < $paso,
                    'las-asistente__pastilla--actual' => $numero === $paso,
                ]) @if ($numero === $paso) aria-current="step" @endif>
                @if ($numero < $paso)<span aria-hidden="true">&check;</span>@endif {{ $texto }}
            </li>
        @endforeach
    </ol>
    <div class="progress las-asistente__barra" role="progressbar" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $porcentaje }}% completado">
        <div class="progress-bar" style="width: {{ $porcentaje }}%"></div>
    </div>
</div>
