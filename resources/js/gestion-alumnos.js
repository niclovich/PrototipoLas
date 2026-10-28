/*
 * P1 Gestión de Alumnos: el botón +/− de cada fila muestra u oculta su detalle (Legajo, Carrera, Estado, Comision,
 * Examen y Acciones), que llega ya renderizado en el campo "acciones" del DataTable.
 */
const $ = window.jQuery;

export function iniciarGestionAlumnos() {
    if (!$ || !$.fn.DataTable) {
        return;
    }

    $(document).on('click', 'table[data-detalle-expandible] [data-expandir]', function () {
        const fila = $(this).closest('tr');
        const api = $(this).closest('table').DataTable();
        const registro = api.row(fila);
        const abierta = registro.child.isShown();

        if (abierta) {
            registro.child.hide();
        } else {
            registro.child(registro.data().acciones, 'las-fila-detalle p-0').show();
        }
        this.setAttribute('aria-expanded', String(!abierta));
        this.querySelector('i').className = abierta ? 'fas fa-plus' : 'fas fa-minus';
    });
}
