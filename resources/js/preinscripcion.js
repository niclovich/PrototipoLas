/*
 * CU1 Preinscripción:
 *  - P2: "Cambiar foto" muestra la vista previa y "Foto cargada correctamente".
 *  - P4: "Adjuntar" muestra el nombre del PDF elegido.
 * Los labels que actúan como botón se activan también con Enter o Espacio (accesibilidad).
 */
function activarConTeclado(label) {
    label.addEventListener('keydown', (evento) => {
        if (evento.key === 'Enter' || evento.key === ' ') {
            evento.preventDefault();
            document.getElementById(label.htmlFor)?.click();
        }
    });
}

function iniciarFoto() {
    const contenedor = document.querySelector('[data-foto-perfil]');
    if (!contenedor) {
        return;
    }
    const input = contenedor.querySelector('[data-foto-input]');
    const vista = contenedor.querySelector('[data-foto-vista]');
    const ok = contenedor.querySelector('[data-foto-ok]');

    input.addEventListener('change', () => {
        const archivo = input.files[0];
        if (!archivo) {
            return;
        }
        vista.src = URL.createObjectURL(archivo);
        vista.classList.remove('d-none');
        ok.classList.remove('d-none');
    });
}

function iniciarAdjuntos() {
    document.querySelectorAll('[data-adjunto]').forEach((tarjeta) => {
        const input = tarjeta.querySelector('[data-adjunto-input]');
        const nombre = tarjeta.querySelector('[data-adjunto-nombre]');
        input.addEventListener('change', () => {
            if (input.files[0]) {
                nombre.textContent = input.files[0].name;
                nombre.classList.remove('d-none');
            }
        });
    });
}

export function iniciarPreinscripcion() {
    iniciarFoto();
    iniciarAdjuntos();
    document.querySelectorAll('label[for][tabindex="0"]').forEach(activarConTeclado);
}
