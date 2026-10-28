{{--
    P2 Registrar Acta de Examen (P2.png) y P2 Modificar Acta de Examen (Modificar_P2.png).
    Variables: $modo ('registrar' | 'modificar'), $accion, $metodo, $titulo, $subtitulo, $boton, $urlCancelar,
    $carreraNombre, $materias (registrar) o $materiaNombre (modificar), $acta (o null), $tribunal [[user_id, rol_tribunal_id]],
    $modalidades, $llamados, $tiposActa, $roles, $docentes, $nombresDocentes, $placeholderDocente, $columnaQuitar.
--}}
@php
    $modificar = $modo === 'modificar';
    $filas = collect(old('tribunal', $tribunal))->values();
@endphp
<form action="{{ $accion }}" method="POST" novalidate data-acta-examen
      data-nombres="{{ json_encode((object) $nombresDocentes) }}" data-roles="{{ json_encode((object) $roles) }}">
    @csrf
    @if ($metodo !== 'POST')
        @method($metodo)
    @endif
    <div class="card card-las-plano">
        <div class="card-body">
            <h2 class="las-titulo-pantalla">{{ $titulo }}</h2>

            <div class="card las-tarjeta">
                <div class="card-body">
                    <h3 class="las-subtitulo las-subtitulo--tarjeta">{{ $subtitulo }}</h3>
                    <div class="row">
                        <div class="col-12 col-md-5">
                            <x-form.input name="carrera_nombre" label="Carrera" :value="$carreraNombre" readonly />
                        </div>
                        <div class="col-12 col-md-4">
                            @if ($modificar)
                                <x-form.input name="materia_nombre" label="Materia" :value="$materiaNombre" readonly />
                            @else
                                <x-form.select name="detalle_plan_estudio_id" label="Materia" :opciones="$materias"
                                               placeholder="Seleccione una materia" :requerido="true" :select2="true" />
                            @endif
                        </div>
                        <div class="col-12 col-md-3">
                            <x-form.date name="fecha_acta" label="Fecha de Examen" :value="$acta?->fecha_acta" :requerido="true" />
                        </div>
                        <div class="col-12 col-md-4">
                            <x-form.select name="modalidad_id" label="Modalidad" :opciones="$modalidades" :value="$acta?->modalidad_id"
                                           placeholder="Seleccione una modalidad" :requerido="true" />
                        </div>
                        <div class="col-12 col-md-4">
                            <x-form.select name="llamado_id" label="Llamado" :opciones="$llamados" :value="$acta?->llamado_id"
                                           placeholder="Seleccione un llamado" :requerido="true" />
                        </div>
                        <div class="col-12 col-md-4">
                            <x-form.select name="tipo_acta_id" label="Tipo de Acta" :opciones="$tiposActa" :value="$acta?->tipo_acta_id"
                                           placeholder="Seleccione un tipo de acta" :requerido="true" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card las-tarjeta">
                <div class="card-body">
                    <h3 class="las-subtitulo las-subtitulo--tarjeta">Tribunal Examinador</h3>
                    <div class="row align-items-end">
                        <div class="col-12 col-md-5">
                            <x-form.select name="nuevo_docente" label="Docente" :opciones="$docentes" :placeholder="$placeholderDocente"
                                           :select2="true" data-nuevo="docente" />
                        </div>
                        <div class="col-12 col-md-4">
                            <x-form.select name="nuevo_rol" label="Rol en el Tribunal" :opciones="$roles" placeholder="Seleccione un rol"
                                           :value="array_key_first($roles)" data-nuevo="rol" />
                        </div>
                        <div class="col-12 col-md-3 form-group">
                            <button type="button" class="btn btn-outline-las btn-block" data-agregar-docente>+ Agregar</button>
                        </div>
                    </div>

                    <table class="table table-bordered table-striped w-100 mb-0" id="tribunal">
                        <thead class="las-thead">
                            <tr>
                                <th scope="col">Docente</th>
                                <th scope="col">Rol</th>
                                <th scope="col">{{ $columnaQuitar }}</th>
                            </tr>
                        </thead>
                        <tbody data-filas-tribunal>
                            @foreach ($filas as $i => $fila)
                                <tr data-docente="{{ $fila['user_id'] ?? '' }}" data-rol="{{ $fila['rol_tribunal_id'] ?? '' }}">
                                    <td>{{ $nombresDocentes[$fila['user_id'] ?? 0] ?? '—' }}</td>
                                    <td>{{ $roles[$fila['rol_tribunal_id'] ?? 0] ?? '—' }}</td>
                                    <td>
                                        <input type="hidden" name="tribunal[{{ $i }}][user_id]" value="{{ $fila['user_id'] ?? '' }}">
                                        <input type="hidden" name="tribunal[{{ $i }}][rol_tribunal_id]" value="{{ $fila['rol_tribunal_id'] ?? '' }}">
                                        <button type="button" class="btn btn-sm btn-las" data-quitar-docente title="Quitar" aria-label="Quitar docente del tribunal">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @error('tribunal')
                        <div class="text-danger small mt-2" role="alert">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent border-0 px-0 d-flex flex-wrap justify-content-end">
            <a href="{{ $urlCancelar }}" class="btn btn-outline-las las-btn-principal mr-2 mb-2">Cancelar</a>
            <button type="submit" class="btn btn-las las-btn-principal mb-2">{{ $boton }}</button>
        </div>
    </div>
</form>

<x-modal-aviso id="modal-tribunal" titulo="Tribunal Examinador" />

{{-- CU 21 alt. 10.1 / CU 22 alt. 12.1: Aceptar → vuelve a la fecha. --}}
<x-modal-aviso-errores :campos="['fecha_acta' => 'Fecha inválida']"
                       :mensajes="['fecha_acta' => \App\Services\Examenes\ActaExamenService::MENSAJE_FECHA]" />
