/*
 * Selects dependientes Provincia → Localidad (<x-select-provincia-localidad>): al elegir la Provincia
 * se piden sus Localidades (JSON [{id, localidad}]) y se reemplazan las opciones.
 */
const $ = window.jQuery;

async function cargarLocalidades(contenedor, provinciaId) {
    const select = contenedor.querySelector('select[data-localidad]');
    const placeholder = select.querySelector('option[value=""]');
    select.innerHTML = '';
    if (placeholder) {
        select.append(placeholder);
    }

    if (provinciaId) {
        const respuesta = await fetch(contenedor.dataset.url.replace('__ID__', provinciaId), { headers: { Accept: 'application/json' } });
        const localidades = respuesta.ok ? await respuesta.json() : [];
        localidades.forEach(({ id, localidad }) => select.append(new Option(localidad, id)));
    }

    select.value = '';
    if ($) {
        $(select).trigger('change');
    }
}

export function iniciarProvinciaLocalidad(contenedor = document) {
    contenedor.querySelectorAll('[data-provincia-localidad]').forEach((grupo) => {
        const provincia = grupo.querySelector('select[data-provincia]');
        // Select2 dispara el evento por jQuery: se escucha con jQuery para cubrir ambos casos.
        ($ ? $(provincia) : null)?.on('change', () => cargarLocalidades(grupo, provincia.value));
    });
}
