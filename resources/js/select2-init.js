/*
 * Select2 (tema Bootstrap 4, español) sobre <select data-select2>. Admite data-placeholder.
 * Se puede llamar con un contenedor (p. ej. un modal recién abierto).
 */
const $ = window.jQuery;

export function iniciarSelect2(contenedor = document) {
    if (!$ || !$.fn.select2) {
        return;
    }

    $(contenedor).find('select[data-select2]').each(function () {
        const $select = $(this);
        const $modal = $select.closest('.modal');

        $select.select2({
            theme: 'bootstrap4',
            language: 'es',
            width: '100%',
            allowClear: !this.required && !this.multiple,
            placeholder: $select.data('placeholder') || 'Seleccione...',
            dropdownParent: $modal.length ? $modal : $(document.body),
        });
    });
}
