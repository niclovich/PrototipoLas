/*
 * DataTables común: server-side por POST (CSRF por $.ajaxSetup), idioma español y Responsive.
 * Se inicializa solo sobre <table data-datatable> (componente <x-datatable>).
 */
const $ = window.jQuery;

export const idiomaEspanol = {
    processing: '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Cargando...',
    search: 'Buscar:',
    lengthMenu: 'Mostrar _MENU_ registros',
    info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
    infoEmpty: 'Mostrando 0 a 0 de 0 registros',
    infoFiltered: '(filtrado de _MAX_ registros)',
    loadingRecords: 'Cargando...',
    zeroRecords: 'No se encontraron resultados',
    emptyTable: 'No hay datos para mostrar',
    paginate: { first: 'Primero', previous: 'Anterior', next: 'Siguiente', last: 'Último' },
    aria: { sortAscending: ': orden ascendente', sortDescending: ': orden descendente' },
};

function leerJson(elemento, atributo, porDefecto) {
    try {
        return JSON.parse(elemento.getAttribute(atributo)) ?? porDefecto;
    } catch {
        return porDefecto;
    }
}

/** Valores de los filtros propios de la pantalla (data-filtros = lista de selectores). */
function valoresFiltros(selectores) {
    return selectores.reduce((valores, selector) => {
        document.querySelectorAll(selector).forEach((campo) => {
            if (campo.name) {
                valores[campo.name] = campo.value;
            }
        });

        return valores;
    }, {});
}

/** Recarga la tabla al cambiar un filtro (texto con espera de 300 ms). */
function escucharFiltros(api, selectores) {
    let espera;
    selectores.forEach((selector) => {
        $(document).on('input change', selector, (evento) => {
            clearTimeout(espera);
            espera = setTimeout(() => api.ajax.reload(), evento.type === 'input' ? 300 : 0);
        });
    });
}

export function crearDataTable(tabla, opciones = {}) {
    const params = leerJson(tabla, 'data-params', {});
    const filtros = leerJson(tabla, 'data-filtros', []);
    const dom = tabla.dataset.dom ? { dom: tabla.dataset.dom } : {};
    // Tablas con detalle propio por fila (P1 Gestión de Alumnos): Responsive solo oculta columnas.
    const responsive = tabla.hasAttribute('data-detalle-expandible') ? { details: false } : true;

    const api = $(tabla).DataTable({
        processing: true,
        serverSide: true,
        responsive,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: idiomaEspanol,
        order: leerJson(tabla, 'data-orden', [[0, 'asc']]),
        columns: leerJson(tabla, 'data-columnas', []),
        ajax: {
            url: tabla.dataset.url,
            type: 'POST',
            data: (datos) => Object.assign(datos, params, valoresFiltros(filtros)),
        },
        ...dom,
        ...opciones,
    });

    escucharFiltros(api, filtros);

    return api;
}

export function iniciarDataTables() {
    if (!$ || !$.fn.DataTable) {
        return;
    }

    document.querySelectorAll('table[data-datatable]').forEach((tabla) => crearDataTable(tabla));

    // Las tablas en pestañas ocultas se recalculan al mostrarse.
    $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', () => {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
    });
}
