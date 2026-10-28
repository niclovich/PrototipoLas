{{--
    P2 Registrar Materia (P2.png) y P2 Modificar Materia (P3.png). Variables: $materia, $modo, $accion, $metodo, $titulo, $boton.
    En Registrar el Estado se muestra fijo en "Activo" y deshabilitado (decisión D1 de E5): el servidor registra "Activo".
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

            <div class="card las-tarjeta">
                <div class="card-body">
                    <h3 class="las-subtitulo las-subtitulo--tarjeta">Datos de la Materia</h3>
                    <div class="row">
                        <div class="col-12 col-md-8">
                            <x-form.input name="nombre_materia" label="Nombre de la Materia" :value="$materia->nombre_materia" :requerido="true"
                                          placeholder="Ej: Matemática" maxlength="150" />
                        </div>
                        <div class="col-12 col-md-4">
                            <x-form.select name="estado_materia" label="Estado" :opciones="\App\Enums\EstadoGeneral::opciones()"
                                           :value="$materia->estado_materia" placeholder="Seleccione un estado" :requerido="! $registrar"
                                           :disabled="$registrar" :title="$registrar ? 'Toda materia nueva se registra en estado Activo' : null" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent border-0 px-0 d-flex flex-wrap justify-content-end">
            <a href="{{ route('panel.materias.index') }}" class="btn btn-outline-las las-btn-principal mr-2 mb-2">Cancelar</a>
            <button type="submit" class="btn btn-las las-btn-principal mb-2">{{ $boton }}</button>
        </div>
    </div>
</form>

{{-- CU 1 alt. 4 / CU 2 alt. 7: "Ya existe una materia con ese nombre". --}}
<x-modal-aviso-errores :campos="['nombre_materia' => 'Materia ya existente']" />
