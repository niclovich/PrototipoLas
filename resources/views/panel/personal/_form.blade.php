{{--
    P2 Registrar Personal (Registrar_P2.png) y P2 Modificar Datos del Personal (Modificar_P2.png).
    Variables: $usuario, $roles, $modo ('registrar' | 'modificar'), $accion, $metodo, $titulo, $boton.
--}}
@php
    $registrar = $modo === 'registrar';
@endphp
<form action="{{ $accion }}" method="POST" novalidate>
    @csrf
    @if ($metodo !== 'POST')
        @method($metodo)
    @endif
    <div class="card card-las-plano">
        <div class="card-body">
            <h2 class="las-titulo-pantalla">{{ $titulo }}</h2>

            <h3 class="las-subtitulo">1 · DATOS PERSONALES</h3>
            <div class="row">
                <div class="col-12 col-md-3">
                    @if ($registrar)
                        <x-form.input name="dni_usuario" label="DNI" :value="$usuario->dni_usuario" :requerido="true" mascara="dni"
                                      placeholder="Ej: 30125478" inputmode="numeric" :data-verificar-dni="route('panel.personal.verificar-dni')" />
                    @else
                        <x-form.input name="dni_usuario" label="DNI" :value="$usuario->dni_usuario" readonly />
                    @endif
                </div>
                <div class="col-12 col-md-4">
                    <x-form.input name="apellido_usuario" label="Apellido" :value="$usuario->apellido_usuario" :requerido="true" maxlength="100" />
                </div>
                <div class="col-12 col-md-5">
                    <x-form.input name="nombre_usuario" label="Nombre" :value="$usuario->nombre_usuario" :requerido="true" maxlength="100" />
                </div>
                <div class="col-12 col-md-3">
                    <x-form.input name="telefono_usuario" type="tel" label="Teléfono" :value="$usuario->telefono_usuario" :requerido="true" maxlength="20" />
                </div>
                <div class="col-12 col-md-5">
                    <x-form.input name="email" type="email" label="Correo" :value="$usuario->email" :requerido="true" maxlength="150" autocomplete="off" />
                </div>
                <div class="col-12 col-md-4">
                    <x-form.input name="direccion_usuario" label="Dirección" :value="$usuario->direccion_usuario" :requerido="true" maxlength="200" />
                </div>
            </div>
            <x-select-provincia-localidad :url-localidades="route('panel.personal.localidades', '__ID__')"
                                          :provincia="$usuario->localidad?->provincia_id" :localidad="$usuario->localidad_id" />

            <h3 class="las-subtitulo">{{ $registrar ? '2 · ROL Y CUENTA DE ACCESO' : '2 · ROL' }}</h3>
            <div class="row">
                <div class="col-12 col-md-4">
                    <x-form.select name="rol_id" label="Rol" :opciones="$roles->pluck('name', 'id')->all()" placeholder="Seleccione un rol"
                                   :value="$usuario->exists ? $usuario->roles->first()?->id : null" :requerido="true" />
                </div>
                <div class="col-12 col-md-8">
                    <x-form.input name="descripcion_rol" label="Descripción del Rol" readonly tabindex="-1"
                                  :data-descripciones-rol="$roles->pluck('descripcion', 'id')->toJson()" />
                </div>
                @if ($registrar)
                    <div class="col-12 col-md-4">
                        <x-form.input name="password" type="password" label="Contraseña" :requerido="true" autocomplete="new-password" />
                    </div>
                    <div class="col-12 col-md-8 d-flex align-items-md-center">
                        <p class="las-ayuda mb-3" id="password-ayuda-lateral">{{ \App\Rules\ContraseniaSegura::AYUDA }}</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-footer bg-transparent border-0 px-0 d-flex flex-wrap justify-content-end">
            <a href="{{ route('panel.personal.index') }}" class="btn btn-outline-las las-btn-principal mr-2 mb-2">Cancelar</a>
            <button type="submit" class="btn btn-las las-btn-principal mb-2">{{ $boton }}</button>
        </div>
    </div>
</form>
