{{--
    Layout del panel. Las vistas definen:
      @section('title', '...')           título de la pestaña del navegador
      @section('encabezado') ... @endsection   normalmente <x-header-with-title>
      @section('contenido') ... @endsection
--}}
@extends('adminlte::page')

@push('css')
    @vite('resources/css/app.css')
@endpush

@push('js')
    @vite('resources/js/app.js')
@endpush

@section('content_header')
    @yield('encabezado')
@stop

@section('content')
    <x-alert />
    @yield('contenido')
@stop

@section('footer')
    <small>{{ config('las.instituto_nombre') }} &middot; {{ config('app.name') }}</small>
@stop
