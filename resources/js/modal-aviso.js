/*
 * Modales automáticos: los avisos devuelven el foco al campo del CU y las confirmaciones
 * enfocan su acción principal. Solo se abre el primer modal marcado con data-abrir.
 */
const $ = window.jQuery;

export function mostrarAviso(id) {
    if ($) {
        $(`#${id}`).modal('show');
    }
}

export function iniciarModalesAviso() {
    if (!$) {
        return;
    }

    $(document).on('hidden.bs.modal', '[data-modal-aviso]', function () {
        const foco = this.dataset.foco ? document.querySelector(this.dataset.foco) : null;
        foco?.focus();
    });

    $(document).on('shown.bs.modal', '[data-modal-confirmacion]', function () {
        this.querySelector('[data-confirmacion-aceptar]')?.focus();
    });

    $('[data-modal-aviso][data-abrir], [data-modal-confirmacion][data-abrir]').first().modal('show');
}
