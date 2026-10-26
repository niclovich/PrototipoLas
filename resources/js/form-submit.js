/*
 * Al enviar un formulario: deshabilita los botones submit y muestra un spinner (evita doble envío).
 * Excluir con <form data-sin-spinner>.
 */
export function iniciarEnvioFormularios() {
    document.addEventListener('submit', (evento) => {
        const formulario = evento.target;

        if (!(formulario instanceof HTMLFormElement) || evento.defaultPrevented || 'sinSpinner' in formulario.dataset) {
            return;
        }

        formulario.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((boton) => {
            boton.disabled = true;
            boton.setAttribute('aria-busy', 'true');
            if (boton.tagName === 'BUTTON') {
                boton.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>');
            }
        });
    });

    // Al volver con el botón "atrás" del navegador, rehabilitar los botones.
    window.addEventListener('pageshow', (evento) => {
        if (evento.persisted) {
            document.querySelectorAll('[aria-busy="true"]').forEach((boton) => {
                boton.disabled = false;
                boton.removeAttribute('aria-busy');
                boton.querySelector('.spinner-border')?.remove();
            });
        }
    });
}
