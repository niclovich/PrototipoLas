{{-- P5 (Registrar) / P3 (Modificar y Baja) Confirmación: mensaje y "Aceptar" → P1 (diagramas de estados). --}}
@extends('layouts.panel')

@section('title', 'Confirmación')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE PERSONAL" />
@endsection

@section('contenido')
    <x-pantalla-confirmacion :mensaje="$mensaje" :url-aceptar="route('panel.personal.index')" />
@endsection
