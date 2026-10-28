/*
 * Gestión de Plan de Estudio (CU 5–8):
 *  - P1: "Seleccionar Carrera" y "Año del Plan de Estudio" recargan P1 (P1c / P1 según el plan activo).
 *  - P1: 🗑 abre el aviso de comisiones dictándose (CU 7 alt. 6) o el modal "Dar de Baja Materia del Plan".
 *  - CU 8 P2: "+ Agregar" suma la correlativa a la tabla y 🗑 la quita.
 */
import { mostrarAviso } from './modal-aviso';

const $ = window.jQuery;

function iniciarCombos() {
    const carrera = document.querySelector('[data-plan-carrera]');
    if (!carrera || !$) {
        return;
    }

    const ir = (params) => {
        window.location.href = `${carrera.dataset.url}?${new URLSearchParams(params)}`;
    };

    $(carrera).on('change', () => ir({ carrera: carrera.value }));
    $(document).on('change', '[data-plan-anio]', function () {
        ir({ carrera: carrera.value, plan: this.value });
    });
}

function iniciarBajaMateria() {
    if (!$) {
        return;
    }

    $(document).on('click', '[data-baja-materia]', function () {
        const datos = JSON.parse(this.dataset.bajaMateria);
        if (datos.comisiones) {
            mostrarAviso('modal-comisiones-dictandose');

            return;
        }

        const modal = document.getElementById('modal-baja-materia');
        modal.querySelector('form').action = datos.action;
        ['carrera', 'plan', 'materia', 'codigo', 'regimen', 'anio', 'carga'].forEach((campo) => {
            modal.querySelector(`[data-campo="${campo}"]`).value = datos[campo] ?? '';
        });
        $(modal).modal('show');
    });
}

function celda(texto) {
    const td = document.createElement('td');
    td.textContent = texto;

    return td;
}

function oculto(nombre, valor) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = nombre;
    input.value = valor;

    return input;
}

function avisar(mensaje) {
    document.querySelector('#modal-correlativa [data-mensaje]').textContent = mensaje;
    mostrarAviso('modal-correlativa');
}

function iniciarCorrelatividades() {
    const formulario = document.querySelector('[data-correlatividades]');
    if (!formulario) {
        return;
    }

    const disponibles = JSON.parse(formulario.dataset.disponibles);
    const condiciones = JSON.parse(formulario.dataset.condiciones);
    const cuerpo = formulario.querySelector('[data-filas-correlativas]');
    const campo = (nombre) => document.querySelector(`[data-nueva="${nombre}"]`);
    let indice = cuerpo.rows.length;

    document.querySelector('[data-agregar-correlativa]').addEventListener('click', () => {
        const id = campo('correlativa').value;
        const cursar = campo('p_cursar').value;
        const rendir = campo('p_rendir').value;

        if (!id || !disponibles[id]) {
            avisar('Debe seleccionar una materia del plan.');

            return;
        }
        if (!cursar || !rendir) {
            avisar('Debe seleccionar la condición para cursar y para rendir.');

            return;
        }
        if (cuerpo.querySelector(`[data-correlativa="${id}"]`)) {
            avisar('La materia ya fue agregada como correlativa.');

            return;
        }

        const fila = document.createElement('tr');
        fila.dataset.correlativa = id;
        fila.append(
            celda(disponibles[id].materia),
            celda(disponibles[id].codigo),
            celda(condiciones[cursar].celda),
            celda(condiciones[rendir].celda),
        );
        const accion = document.createElement('td');
        accion.append(
            oculto(`correlativas[${indice}][detalle_plan_correlativa_id]`, id),
            oculto(`correlativas[${indice}][p_cursar]`, cursar),
            oculto(`correlativas[${indice}][p_rendir]`, rendir),
        );
        accion.insertAdjacentHTML('beforeend', '<button type="button" class="btn btn-sm btn-las" data-quitar-correlativa title="Quitar" '
            + 'aria-label="Quitar correlativa"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>');
        fila.append(accion);
        cuerpo.append(fila);
        indice += 1;
        campo('correlativa').value = '';
    });

    cuerpo.addEventListener('click', (evento) => {
        evento.target.closest('[data-quitar-correlativa]')?.closest('tr').remove();
    });
}

export function iniciarPlanEstudio() {
    iniciarCombos();
    iniciarBajaMateria();
    iniciarCorrelatividades();
}
