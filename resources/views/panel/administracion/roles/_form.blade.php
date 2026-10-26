{{-- Formulario compartido entre alta y modificación de roles. Variables: $rol, $gruposPermisos, $accion, $metodo. --}}
@php($asignados = $rol->exists ? $rol->permissions->pluck('name')->all() : [])
<form action="{{ $accion }}" method="POST" novalidate>
    @csrf
    @if ($metodo !== 'POST')
        @method($metodo)
    @endif
    <div class="card card-outline card-las">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-5">
                    @if ($rol->esDelSistema())
                        <x-form.input name="name_lectura" label="Nombre del rol" :value="$rol->name" disabled
                                      ayuda="Rol de la tesis: el nombre no se modifica." />
                    @else
                        <x-form.input name="name" label="Nombre del rol" :value="$rol->name" :requerido="true" maxlength="100" />
                    @endif
                </div>
                <div class="col-12 col-md-7">
                    <x-form.input name="descripcion" label="Descripción" :value="$rol->descripcion" maxlength="255" />
                </div>
            </div>

            <h2 class="h6 mt-2 mb-3">Permisos</h2>
            @error('permisos.*')
                <span class="invalid-feedback d-block mb-2" role="alert">{{ $message }}</span>
            @enderror
            <div class="row">
                @foreach ($gruposPermisos as $entidad => $permisos)
                    <div class="col-12 col-sm-6 col-lg-4 mb-3">
                        <fieldset class="border rounded p-2 h-100">
                            <legend class="w-auto px-2 h6 mb-0">{{ $entidad }}</legend>
                            @foreach ($permisos as $permiso => $accion)
                                <x-form.checkbox name="permisos[]" :value="$permiso" :label="$accion" :tooltip="$permiso"
                                                 :checked="in_array($permiso, $asignados, true)" />
                            @endforeach
                        </fieldset>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-end">
            <a href="{{ route('panel.roles.index') }}" class="btn btn-secondary mr-2 mb-1">Cancelar</a>
            <button type="submit" class="btn btn-las mb-1"><i class="fas fa-save" aria-hidden="true"></i> Guardar</button>
        </div>
    </div>
</form>
