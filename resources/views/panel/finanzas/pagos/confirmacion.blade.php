{{-- CU7 P3 Confirmación (CU7_pago_cuotas/wireframes/P3.png): paso 23, "Descargar Comprobante" y "Aceptar" (→ P1 Gestión de Alumnos). --}}
@extends('layouts.panel')

@section('title', 'Registrar Pago de Cuotas')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Finanzas\PagoCuotaController::ENCABEZADO" />
@endsection

@section('contenido')
    <x-pantalla-confirmacion :titulo="\App\Services\Finanzas\PagoCuotasService::CONFIRMACION" :mensaje="\App\Services\Finanzas\PagoCuotasService::CONFIRMACION_DETALLE"
                             :url-aceptar="route('panel.alumnos.index')">
        @can('exportar_pago')
            <x-slot name="acciones">
                <a href="{{ route('panel.pagos.comprobante', $factura) }}" class="btn btn-outline-las las-btn-principal px-5">
                    <i class="fas fa-download mr-1" aria-hidden="true"></i> Descargar Comprobante
                </a>
            </x-slot>
        @endcan
    </x-pantalla-confirmacion>
@endsection
