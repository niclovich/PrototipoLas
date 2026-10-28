{{-- CU 26: P2 Modificar Datos del Personal + modal "Datos inválidos" (alt. 14). --}}
@extends('layouts.panel')

@section('title', 'Modificar Datos del Personal')

@section('encabezado')
    <x-header-with-title titulo="GESTIÓN DE PERSONAL" />
@endsection

@section('contenido')
    @include('panel.personal._form', [
        'modo' => 'modificar',
        'accion' => route('panel.personal.update', $usuario),
        'metodo' => 'PUT',
        'titulo' => 'Modificar Datos del Personal',
        'boton' => 'Guardar Cambios',
    ])

    <x-modal-aviso id="modal-datos-invalidos" titulo="Datos inválidos" :mensaje="\App\Services\Personal\PersonalService::MENSAJE_DATOS_INVALIDOS"
                   :abrir="$errors->any()" foco="#apellido_usuario" />
@endsection
