/*
 * Comportamientos genéricos de pantallas server-side:
 *  - <select data-autoenviar>: al cambiar, envía su formulario (combos de CU3 y CU4: "se actualiza la tabla").
 *  - <input data-filtro-tabla="#tabla">: filtra las filas de la tabla por el texto (CU4 P3 "Filtrar").
 */
export function iniciarAutoenvio() {
    document.querySelectorAll('select[data-autoenviar]').forEach((combo) => {
        combo.addEventListener('change', () => combo.form?.submit());
    });

    document.querySelectorAll('input[data-filtro-tabla]').forEach((filtro) => {
        const filas = () => document.querySelectorAll(`${filtro.dataset.filtroTabla} tbody tr[data-fila]`);
        filtro.addEventListener('input', () => {
            const texto = filtro.value.trim().toLowerCase();
            filas().forEach((fila) => {
                fila.classList.toggle('d-none', texto !== '' && !fila.textContent.toLowerCase().includes(texto));
            });
        });
    });
}
