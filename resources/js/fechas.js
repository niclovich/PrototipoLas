/*
 * Datepicker en español (Tempus Dominus Bootstrap 4 + moment) sobre [data-datepicker] (<x-form.date>).
 * Formato dd/mm/aaaa.
 */
const $ = window.jQuery;

export function iniciarFechas(contenedor = document) {
    if (!$ || !$.fn.datetimepicker) {
        return;
    }

    if (window.moment) {
        window.moment.locale('es');
    }

    $(contenedor).find('[data-datepicker]').each(function () {
        $(this).datetimepicker({
            format: 'DD/MM/YYYY',
            locale: 'es',
            useCurrent: false,
            icons: { time: 'far fa-clock', date: 'far fa-calendar-alt' },
            buttons: { showToday: true, showClear: true, showClose: true },
            tooltips: {
                today: 'Hoy', clear: 'Borrar', close: 'Cerrar', selectMonth: 'Elegir mes',
                prevMonth: 'Mes anterior', nextMonth: 'Mes siguiente', selectYear: 'Elegir año',
                prevYear: 'Año anterior', nextYear: 'Año siguiente',
            },
        });
    });
}
