/*
 * Gestión de Personal (CU 25, 26, 27):
 *  - P2 Registrar: al salir del DNI se verifica si ya existe (paso 4) y se abre el modal "DNI ya registrado".
 *  - P2 Registrar/Modificar: "Descripción del Rol" se completa al elegir el Rol (pasos 11–12).
 *  - P1: el ícono de baja llena el modal "Registrar Baja de Personal" con los datos del usuario (paso 5).
 */
import { mostrarAviso } from './modal-aviso';

const $ = window.jQuery;

function iniciarVerificacionDni() {
    const dni = document.querySelector('[data-verificar-dni]');
    if (!dni) {
        return;
    }

    dni.addEventListener('blur', async () => {
        const valor = dni.value.replace(/\D/g, '');
        if (valor.length < 7) {
            return;
        }

        const respuesta = await fetch(dni.dataset.verificarDni, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ dni_usuario: valor }),
        });

        if (respuesta.ok && (await respuesta.json()).existe) {
            mostrarAviso('modal-dni-registrado');
        }
    });
}

function iniciarDescripcionRol() {
    const descripcion = document.querySelector('[data-descripciones-rol]');
    const rol = document.querySelector('select[name="rol_id"]');
    if (!descripcion || !rol) {
        return;
    }

    const descripciones = JSON.parse(descripcion.dataset.descripcionesRol);
    const actualizar = () => {
        descripcion.value = descripciones[rol.value] ?? '';
    };

    ($ ? $(rol) : null)?.on('change', actualizar);
    actualizar();
}

function iniciarModalBaja() {
    if (!$) {
        return;
    }

    $(document).on('click', '[data-baja]', function () {
        const modal = document.getElementById('modal-baja');
        const datos = this.dataset;
        modal.querySelector('form').action = datos.action;
        modal.querySelector('[name="usuario_id"]').value = datos.id;
        ['dni', 'nombre', 'rol', 'alta', 'estado'].forEach((campo) => {
            modal.querySelector(`[data-campo="${campo}"]`).value = datos[campo] ?? '';
        });
        modal.querySelector('[name="motivo_id"]').value = '';
        modal.querySelector('[name="observacion_novedad"]').value = '';
        modal.querySelectorAll('.is-invalid').forEach((campo) => campo.classList.remove('is-invalid'));
        modal.querySelectorAll('.invalid-feedback').forEach((error) => error.remove());
        $(modal).modal('show');
    });
}

export function iniciarPersonal() {
    iniciarVerificacionDni();
    iniciarDescripcionRol();
    iniciarModalBaja();
}
