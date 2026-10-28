{{-- Confirmación de los ABM con wireframe (Carreras, Materias, …). Variables: $encabezado, $titulo, $lineas, $urlAceptar; opcionales $mensaje y $tituloLineas. --}}
@extends('layouts.panel')

@section('title', $titulo)

@section('encabezado')
    <x-header-with-title :titulo="$encabezado" />
@endsection

@section('contenido')
    <x-pantalla-confirmacion :titulo="$titulo" :mensaje="$mensaje ?? null" :titulo-lineas="$tituloLineas ?? null" :lineas="$lineas" :url-aceptar="$urlAceptar" />
@endsection
