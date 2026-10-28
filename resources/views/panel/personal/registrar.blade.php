{{-- CU 25: P2 Registrar Personal + modales P3 "DNI ya registrado" y P4 "Contraseña inválida". --}}
@extends('layouts.panel')

@section('title', 'Registrar Personal')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE PERSONAL" />
@endsection

@section('contenido')
    @include('panel.personal._form', [
        'modo' => 'registrar',
        'accion' => route('panel.personal.store'),
        'metodo' => 'POST',
        'titulo' => 'Registrar Personal',
        'boton' => 'Registrar Personal',
    ])

    {{-- Alt. 4 antes que alt. 15 (orden de la especificación). --}}
    @php
        $dniRegistrado = $errors->first('dni_usuario') === \App\Services\Personal\PersonalService::MENSAJE_DNI_REGISTRADO;
    @endphp
    <x-modal-aviso id="modal-dni-registrado" titulo="DNI ya registrado" :mensaje="\App\Services\Personal\PersonalService::MENSAJE_DNI_REGISTRADO"
                   :abrir="$dniRegistrado" foco="#dni_usuario" />
    <x-modal-aviso id="modal-contrasenia-invalida" titulo="Contraseña inválida" :mensaje="\App\Rules\ContraseniaSegura::MENSAJE"
                   :abrir="! $dniRegistrado && $errors->has('password')" foco="#password" />
@endsection
