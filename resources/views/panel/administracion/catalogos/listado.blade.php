@extends('layouts.panel')

@section('title', 'Catálogos')

@section('encabezado')
    <x-header-with-title titulo="Catálogos" subtitulo="Valores que usan los formularios del sistema. Elija un catálogo para administrarlo." />
@endsection

@section('contenido')
    <div class="row">
        @foreach ($catalogos as $catalogo)
            <div class="col-12 col-sm-6 col-lg-4 mb-3">
                <a href="{{ route('panel.catalogos.registros.index', $catalogo->clave) }}" class="card card-outline card-las h-100 mb-0 text-reset las-card-enlace">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-list fa-lg text-las mr-3" aria-hidden="true"></i>
                        <span class="font-weight-bold">{{ $catalogo->titulo }}</span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
