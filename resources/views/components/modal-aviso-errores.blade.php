{{--
    Escenarios alternativos "mensaje → Aceptar → vuelve al paso N": abre un <x-modal-aviso> con el PRIMER campo
    de la lista (orden de la especificación) que tenga error, y al cerrar pone el foco en ese campo.
    campos: [nombre del campo => título del modal]. El mensaje es el error del campo (texto exacto de la spec).
    mensajes (opcional): [nombre del campo => mensaje de la spec]; si se indica, ese campo abre el modal solo con ese
    mensaje (los demás errores del campo, sin escenario en la spec, quedan debajo del campo).
--}}
@props(['campos', 'mensajes' => [], 'id' => 'modal-aviso-errores'])
@php
    $campo = collect(array_keys($campos))->first(fn (string $nombre) => $errors->has($nombre)
        && (! isset($mensajes[$nombre]) || $errors->first($nombre) === $mensajes[$nombre]));
@endphp
@if ($campo)
    <x-modal-aviso :id="$id" :titulo="$campos[$campo]" :mensaje="$errors->first($campo)" :abrir="true"
                   :foco="'#'.\App\Support\CampoFormulario::id($campo)" />
@endif
