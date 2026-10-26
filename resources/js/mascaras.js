/*
 * Máscaras de entrada (Inputmask) sobre [data-mascara]:
 *  - dni:    7 u 8 dígitos.
 *  - monto:  $ 1.234,56 (se envía sin máscara: 1234.56).
 *  - entero: solo dígitos.
 */
const $ = window.jQuery;

const mascaras = {
    dni: { regex: '\\d{7,8}', placeholder: '' },
    monto: {
        alias: 'numeric',
        groupSeparator: '.',
        radixPoint: ',',
        digits: 2,
        digitsOptional: false,
        prefix: '$ ',
        rightAlign: false,
    },
    entero: { regex: '\\d*', placeholder: '' },
};

export function formatearMonto(valor) {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', minimumFractionDigits: 2 }).format(Number(valor) || 0);
}

export function iniciarMascaras(contenedor = document) {
    if (!$ || !$.fn.inputmask) {
        return;
    }

    $(contenedor).find('[data-mascara]').each(function () {
        const config = mascaras[this.dataset.mascara];
        if (config) {
            $(this).inputmask(config);
        }
    });

    // Al enviar, los montos viajan con punto decimal (1234.56), como los espera el servidor.
    $(contenedor).find('form').on('submit', function () {
        $(this).find('[data-mascara="monto"]').each(function () {
            const valor = $(this).inputmask('unmaskedvalue');
            $(this).inputmask('remove');
            this.value = valor.replace(/\./g, '').replace(',', '.');
        });
    });
}
