/*
 * Confirmaciones:
 *  - <form data-confirm="texto">: SweetAlert2 antes de enviar (acciones destructivas o irreversibles).
 *  - <x-modal-confirmar>: el botón que lo abre (data-action) define la URL del formulario del modal.
 */
const $ = window.jQuery;

export function confirmar(texto, { titulo = '¿Está seguro?', boton = 'Sí, continuar' } = {}) {
    if (!window.Swal) {
        return Promise.resolve(window.confirm(texto));
    }

    return window.Swal.fire({
        title: titulo,
        text: texto,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: boton,
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true,
        customClass: { confirmButton: 'btn btn-las ml-2', cancelButton: 'btn btn-secondary' },
        buttonsStyling: false,
    }).then((resultado) => resultado.isConfirmed === true || resultado.value === true);
}

export function iniciarConfirmaciones() {
    document.addEventListener('submit', (evento) => {
        const formulario = evento.target;

        if (!(formulario instanceof HTMLFormElement) || !formulario.dataset.confirm || formulario.dataset.confirmado === '1') {
            return;
        }

        evento.preventDefault();
        evento.stopImmediatePropagation();

        confirmar(formulario.dataset.confirm).then((aceptado) => {
            if (aceptado) {
                formulario.dataset.confirmado = '1';
                formulario.requestSubmit();
            }
        });
    }, true);

    if ($) {
        $(document).on('show.bs.modal', '[data-modal-confirmar]', function (evento) {
            const accion = evento.relatedTarget?.dataset.action;
            if (accion) {
                this.querySelector('form').setAttribute('action', accion);
            }
        });
    }
}
