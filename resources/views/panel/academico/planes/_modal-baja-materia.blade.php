{{-- CU 7 P2 Modal Dar de Baja Materia del Plan (Baja_Materia_P2.png). Lo llena resources/js/plan-estudio.js con los datos de la fila. --}}
<div class="modal fade las-modal" id="modal-baja-materia" tabindex="-1" role="dialog" aria-labelledby="modal-baja-materia-titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <form method="POST" action="#" class="modal-content" novalidate>
            @csrf
            <div class="modal-header las-modal__cabecera">
                <h5 class="modal-title" id="modal-baja-materia-titulo">Dar de Baja Materia del Plan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <h6 class="las-subtitulo">DATOS DE LA MATERIA</h6>
                <div class="row">
                    <div class="col-12 col-md-8"><x-form.input name="baja_carrera" label="Carrera" readonly data-campo="carrera" /></div>
                    <div class="col-12 col-md-4"><x-form.input name="baja_plan" label="Año del Plan" readonly data-campo="plan" /></div>
                    <div class="col-12 col-md-8"><x-form.input name="baja_materia" label="Materia" readonly data-campo="materia" /></div>
                    <div class="col-12 col-md-4"><x-form.input name="baja_codigo" label="Código" readonly data-campo="codigo" /></div>
                    <div class="col-12 col-md-4"><x-form.input name="baja_regimen" label="Régimen" readonly data-campo="regimen" /></div>
                    <div class="col-12 col-md-4"><x-form.input name="baja_anio" label="Año" readonly data-campo="anio" /></div>
                    <div class="col-12 col-md-4"><x-form.input name="baja_carga" label="Carga Horaria" readonly data-campo="carga" /></div>
                </div>

                <h6 class="las-subtitulo mt-2">DATOS DE LA BAJA</h6>
                <div class="row">
                    <div class="col-12 col-md-4">
                        <x-form.input name="baja_fecha" label="Fecha de Baja" :value="today()->format('d/m/Y')" readonly />
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
