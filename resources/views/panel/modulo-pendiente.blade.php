@extends('layouts.panel')

@section('title', $titulo)

@section('encabezado')
    <x-header-with-title :titulo="$titulo" />
@endsection

@section('contenido')
    <div class="card card-outline card-las">
        <div class="card-body text-center py-5">
            <i class="fas fa-tools fa-3x text-muted mb-3" aria-hidden="true"></i>
            <p class="mb-0">Este módulo se encuentra en desarrollo.</p>
        </div>
    </div>
@endsection
