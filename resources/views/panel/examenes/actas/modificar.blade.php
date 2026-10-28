{{-- CU 22: P2 Formulario Modificar Acta Examen (Modificar_P2.png, Fig. 236). Carrera y Materia no cambian. --}}
@extends('layouts.panel')

@section('title', 'Modificar Acta de Examen')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE ACTAS EXAMEN" />
@endsection

@section('contenido')
    @include('panel.examenes.actas._form', [
        'modo' => 'modificar',
        'accion' => route('panel.actas.update', $acta),
        'metodo' => 'PUT',
        'titulo' => 'Modificar Acta de Examen',
        'subtitulo' => 'Datos del Acta · Nro Acta '.$acta->nro_acta_texto,
        'boton' => 'Guardar Cambios',
        'urlCancelar' => route('panel.actas.index', ['carrera' => $acta->detallePlan->planEstudio->carrera_id]),
        'carreraNombre' => $acta->detallePlan->planEstudio->carrera->nombre_carrera,
        'materiaNombre' => $acta->detallePlan->materia->nombre_materia,
        'placeholderDocente' => 'Seleccione un docente de la materia',
        'columnaQuitar' => 'Quitar',
    ])
@endsection
