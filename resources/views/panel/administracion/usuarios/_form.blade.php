{{-- Formulario compartido entre alta y modificación. Variables: $usuario, $roles, $accion, $metodo. --}}
<form action="{{ $accion }}" method="POST" novalidate>
    @csrf
    @if ($metodo !== 'POST')
        @method($metodo)
    @endif
    <div class="card card-outline card-las">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-6">
                    <x-form.input name="apellido_usuario" label="Apellido" :value="$usuario->apellido_usuario" :requerido="true" maxlength="100" />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.input name="nombre_usuario" label="Nombre" :value="$usuario->nombre_usuario" :requerido="true" maxlength="100" />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.input name="dni_usuario" label="DNI" :value="$usuario->dni_usuario" :requerido="true" mascara="dni"
                                  placeholder="12345678" inputmode="numeric" />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.input name="email" type="email" label="Correo electrónico" :value="$usuario->email" :requerido="true"
                                  maxlength="150" ayuda="Es el usuario para ingresar al sistema." />
                </div>
                <div class="col-12 col-md-6">
                    <x-form.select name="rol" label="Rol" :opciones="$roles" :value="$usuario->exists ? $usuario->nombreRol() : null" :requerido="true" />
                </div>
            </div>
            @unless ($usuario->exists)
                <p class="text-muted mb-0">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    El usuario recibirá por correo un enlace (válido {{ config('las.link_alta_horas') }} horas, de un solo uso) para definir su contraseña.
                </p>
            @endunless
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-end">
            <a href="{{ route('panel.usuarios.index') }}" class="btn btn-secondary mr-2 mb-1">Cancelar</a>
            <button type="submit" class="btn btn-las mb-1"><i class="fas fa-save" aria-hidden="true"></i> Guardar</button>
        </div>
    </div>
</form>
