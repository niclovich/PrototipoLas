{{--
    P2 Modal Registrar Baja de Personal (wireframe Baja_P2.png). Lo llena resources/js/personal.js con los datos de la fila;
    si la baja vuelve con errores, se reabre con el mismo usuario ($usuarioBaja).
--}}
@php
    $abrir = $usuarioBaja !== null;
    $valor = fn (string $campo) => match ($campo) {
        'dni' => $usuarioBaja?->dni_usuario,
        'nombre' => $usuarioBaja?->nombre_completo,
        'rol' => $usuarioBaja?->nombreRol(),
        'alta' => $usuarioBaja?->fecha_alta?->format('d/m/Y'),
        'estado' => $usuarioBaja?->estado_cuenta->label(),
    };
@endphp
<div class="modal fade las-modal" id="modal-baja" tabindex="-1" role="dialog" aria-labelledby="modal-baja-titulo" aria-hidden="true"
     @if ($abrir) data-modal-aviso data-abrir @endif>
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form method="POST" action="{{ $abrir ? route('panel.personal.baja', $usuarioBaja) : '#' }}" class="modal-content" novalidate>
            @csrf
            <input type="hidden" name="usuario_id" value="{{ $usuarioBaja?->id }}">
            <div class="modal-header las-modal__cabecera">
                <h5 class="modal-title" id="modal-baja-titulo">Registrar Baja de Personal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <h6 class="las-subtitulo">DATOS DEL USUARIO</h6>
                <div class="row">
                    <div class="col-12 col-md-3">
                        <x-form.input name="baja_dni" label="DNI" :value="$valor('dni')" readonly data-campo="dni" />
                    </div>
                    <div class="col-12 col-md-5">
                        <x-form.input name="baja_nombre" label="Apellido y Nombre" :value="$valor('nombre')" readonly data-campo="nombre" />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-form.input name="baja_rol" label="Rol" :value="$valor('rol')" readonly data-campo="rol" />
                    </div>
                    <div class="col-12 col-md-3">
                        <x-form.input name="baja_alta" label="Fecha de Alta" :value="$valor('alta')" readonly data-campo="alta" />
                    </div>
                    <div class="col-12 col-md-3">
                        <x-form.input name="baja_estado" label="Estado Cuenta" :value="$valor('estado')" readonly data-campo="estado" />
                    </div>
                </div>

                <h6 class="las-subtitulo mt-2">DATOS DE LA BAJA</h6>
                <div class="row">
                    <div class="col-12 col-md-3">
                        <x-form.input name="baja_fecha" label="Fecha" :value="today()->format('d/m/Y')" readonly />
                    </div>
                    <div class="col-12 col-md-9">
                        <x-form.select name="motivo_id" label="Motivo de Baja" :opciones="$motivos->pluck('descripcion', 'id')->all()"
                                       placeholder="Seleccione un motivo de baja" :requerido="true" />
                    </div>
                    <div class="col-12">
                        <x-form.textarea name="observacion_novedad" label="Observación" placeholder="Ingrese una observación.." :filas="3" maxlength="1000" />
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-las px-5" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-las px-5">Confirmar Baja</button>
            </div>
        </form>
    </div>
</div>
