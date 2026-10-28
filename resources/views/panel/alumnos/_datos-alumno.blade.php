{{-- Tarjeta "Datos del Alumno" de CU3 P2/P3, Cambio de Comisión y CU5 P2: Legajo · Apellido y Nombre · Carrera (solo lectura). --}}
<div class="card las-tarjeta mb-4">
    <div class="card-body">
        <h3 class="las-subtitulo las-subtitulo--tarjeta">Datos del Alumno</h3>
        <div class="row">
            <div class="col-12 col-md-3">
                <x-form.input name="legajo" label="Legajo" :value="$alumno->nro_legajo" readonly />
            </div>
            <div class="col-12 col-md-4">
                <x-form.input name="apellido_nombre" label="Apellido y Nombre" :value="$alumno->usuario->nombre_completo" readonly />
            </div>
            <div class="col-12 col-md-5">
                <x-form.input name="carrera" label="Carrera" :value="$alumno->carrera->nombre_carrera" readonly />
            </div>
        </div>
    </div>
</div>
