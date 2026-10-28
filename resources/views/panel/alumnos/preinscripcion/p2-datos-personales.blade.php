{{-- CU1 P2 Datos personales (Paso_2_Datos_Personales.png, Fig. 63) = CU 9 pasos 2–3. Alt. 4.1 "DNI Inexistente" en modal. --}}
@extends('layouts.panel')

@section('title', 'Preinscripción · Datos personales')

@section('encabezado')
    <x-header-with-title :titulo="\App\Http\Controllers\Panel\Alumnos\PreinscripcionController::ENCABEZADO" />
@endsection

@section('contenido')
    @include('panel.alumnos.preinscripcion._pasos', ['paso' => 2])

    <form action="{{ route('panel.preinscripcion.datos-personales.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        <h2 class="h4 font-weight-bold mb-1">Datos personales</h2>
        <p class="text-muted">Completa la ficha basica del nuevo alumno.</p>
        <div class="row">
            <div class="col-12 col-lg-9">
                <h3 class="las-subtitulo">IDENTIFICACION</h3>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <x-form.input name="apellido_usuario" label="Apellido" :value="$datos['apellido_usuario'] ?? null" :requerido="true" maxlength="100" />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-form.input name="nombre_usuario" label="Nombre" :value="$datos['nombre_usuario'] ?? null" :requerido="true" maxlength="100" />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-form.input name="dni_usuario" label="DNI" :value="$dni" readonly tabindex="-1" />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-form.date name="fecha_nacimiento" label="Fecha de nacimiento" :value="$datos['fecha_nacimiento'] ?? null" />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-form.select name="sexo" label="Sexo" :opciones="$sexos" :value="$datos['sexo'] ?? null" />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-form.select name="estado_civil" label="Estado civil" :opciones="$estadosCiviles" :value="$datos['estado_civil'] ?? null" />
                    </div>
                </div>

                <h3 class="las-subtitulo">CONTACTO</h3>
                <div class="row">
                    <div class="col-12 col-md-5">
                        <x-form.input name="email" type="email" label="Correo electronico" :value="$datos['email'] ?? null" :requerido="true" maxlength="150" autocomplete="off" />
                    </div>
                    <div class="col-12 col-md-4">
                        <x-form.input name="telefono_usuario" type="tel" label="Telefono" :value="$datos['telefono_usuario'] ?? null" maxlength="20" />
                    </div>
                    <div class="col-12 col-md-3">
                        <x-form.input name="celular_usuario" type="tel" label="Celular" :value="$datos['celular_usuario'] ?? null" maxlength="20" />
                    </div>
                </div>

                <h3 class="las-subtitulo">DOMICILIO</h3>
                <x-form.input name="direccion_usuario" label="Direccion" :value="$datos['direccion_usuario'] ?? null" maxlength="200" />
                <x-select-provincia-localidad :url-localidades="route('panel.preinscripcion.localidades', '__ID__')" columna="col-12 col-md-4"
                                              :provincia="$provinciaId" :localidad="$localidadId">
                    <div class="col-12 col-md-4">
                        <x-form.input name="barrio_usuario" label="Barrio" :value="$datos['barrio_usuario'] ?? null" maxlength="100" />
                    </div>
                </x-select-provincia-localidad>
            </div>

            <div class="col-12 col-lg-3">
                <div class="las-foto-perfil text-center" data-foto-perfil>
                    <h3 class="las-subtitulo text-center mt-0">FOTO DE PERFIL</h3>
                    <div class="las-foto-perfil__circulo mx-auto mb-2">
                        <img src="{{ $foto ? \Illuminate\Support\Facades\Storage::disk('public')->url($foto) : '' }}" alt="Vista previa de la foto de perfil"
                             @class(['d-none' => ! $foto]) data-foto-vista>
                    </div>
                    <p @class(['text-success small mb-2', 'd-none' => ! $foto]) data-foto-ok>Foto cargada correctamente</p>
                    <label for="foto" class="btn btn-outline-las mb-1" tabindex="0">Cambiar foto</label>
                    <input type="file" name="foto" id="foto" class="las-input-oculto" accept="image/jpeg,image/png" data-foto-input>
                    @error('foto')
                        <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        @include('panel.alumnos.preinscripcion._botones', ['urlAtras' => route('panel.preinscripcion.create')])
    </form>

    <x-modal-aviso id="modal-dni-inexistente" titulo="DNI Inexistente" :mensaje="\App\Services\Alumnos\PreinscripcionService::MENSAJE_DNI_INEXISTENTE"
                   :abrir="(bool) session(\App\Http\Controllers\Panel\Alumnos\PreinscripcionController::AVISO_DNI_INEXISTENTE)" foco="#apellido_usuario" />
@endsection
