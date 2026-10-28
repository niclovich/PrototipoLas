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
    <div class="las-footer-contenido">
        <small>&copy; {{ now()->year }} {{ config('las.instituto_nombre') }}</small>
        <small>{{ config('app.name') }} &middot; Gestión académica</small>
    </div>
@stop
