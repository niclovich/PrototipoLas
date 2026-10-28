/*
 * P2 Registrar / Modificar Acta de Examen (CU 21, CU 22): "+ Agregar" suma un docente con su rol al
 * Tribunal Examinador y 🗑 lo quita previa confirmación (CU 22 alt. 24.2). Las filas viajan como tribunal[i][...].
 */
import { mostrarAviso } from './modal-aviso';
import { confirmar } from './confirmar';

const $ = window.jQuery;

function avisar(mensaje) {
    document.querySelector('#modal-tribunal [data-mensaje]').textContent = mensaje;
    mostrarAviso('modal-tribunal');
}

function oculto(nombre, valor) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = nombre;
    input.value = valor;

    return input;
}

function celda(texto) {
    const td = document.createElement('td');
    td.textContent = texto;

    return td;
}

export function iniciarActaExamen() {
    const formulario = document.querySelector('[data-acta-examen]');
    if (!formulario) {
        return;
    }

    const nombres = JSON.parse(formulario.dataset.nombres);
    const roles = JSON.parse(formulario.dataset.roles);
    const cuerpo = formulario.querySelector('[data-filas-tribunal]');
    const docente = formulario.querySelector('[data-nuevo="docente"]');
    const rol = formulario.querySelector('[data-nuevo="rol"]');
    let indice = cuerpo.rows.length;

    formulario.querySelector('[data-agregar-docente]').addEventListener('click', () => {
        if (!docente.value || !nombres[docente.value]) {
            avisar('Debe seleccionar un docente.');

            return;
        }
        if (!rol.value) {
            avisar('Debe seleccionar un rol del tribunal.');

            return;
        }
        if (cuerpo.querySelector(`[data-docente="${docente.value}"]`)) {
            avisar('Un docente no puede integrar el tribunal más de una vez.');

            return;
        }
        if (cuerpo.querySelector(`[data-rol="${rol.value}"]`)) {
            avisar('Cada rol del tribunal puede asignarse una sola vez.');

            return;
        }

        const fila = document.createElement('tr');
        fila.dataset.docente = docente.value;
        fila.dataset.rol = rol.value;
        fila.append(celda(nombres[docente.value]), celda(roles[rol.value]));
        const accion = document.createElement('td');
        accion.append(
            oculto(`tribunal[${indice}][user_id]`, docente.value),
            oculto(`tribunal[${indice}][rol_tribunal_id]`, rol.value),
        );
        accion.insertAdjacentHTML('beforeend', '<button type="button" class="btn btn-sm btn-las" data-quitar-docente title="Quitar" '
            + 'aria-label="Quitar docente del tribunal"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>');
        fila.append(accion);
        cuerpo.append(fila);
        indice += 1;

        if ($) {
            $(docente).val('').trigger('change');
        } else {
            docente.value = '';
        }
    });

    cuerpo.addEventListener('click', (evento) => {
        const fila = evento.target.closest('[data-quitar-docente]')?.closest('tr');
        if (!fila) {
            return;
        }
        const nombre = fila.cells[0].textContent;
        confirmar(`¿Quitar a ${nombre} del Tribunal?`, { boton: 'Sí, quitar' }).then((aceptado) => {
            if (aceptado) {
                fila.remove();
            }
        });
    });
}
