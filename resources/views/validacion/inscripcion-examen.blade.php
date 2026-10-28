{{-- Validación pública del Comprobante de Inscripción a Examen de CU5 (QR). Solo datos mínimos. --}}
@extends('validacion.layout', ['tituloPagina' => 'Validación de inscripción a examen', 'tituloTarjeta' => 'Inscripción a examen válida'])

@section('datos')
    <dt class="col-sm-5">Alumno</dt><dd class="col-sm-7">{{ $detalle->alumno->usuario->nombre_completo }} (Legajo {{ $detalle->alumno->nro_legajo }})</dd>
    <dt class="col-sm-5">Materia</dt><dd class="col-sm-7">{{ $detalle->acta->detallePlan->materia->nombre_materia }}</dd>
    <dt class="col-sm-5">Nro Acta</dt><dd class="col-sm-7">{{ $detalle->acta->nro_acta_texto }}</dd>
    <dt class="col-sm-5">Llamado</dt><dd class="col-sm-7">{{ $detalle->acta->llamado->llamado }}</dd>
    <dt class="col-sm-5">Fecha de examen</dt><dd class="col-sm-7">{{ $detalle->acta->fecha_acta->format('d/m/Y') }}</dd>
    <dt class="col-sm-5">Estado</dt><dd class="col-sm-7"><x-badge-estado :estado="$detalle->estado_detalle_acta" /></dd>
@endsection
