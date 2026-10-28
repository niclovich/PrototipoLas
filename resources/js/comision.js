/*
 * Combos "Seleccionar Carrera" que recargan P1 con la carrera elegida (Comisiones, Cuotas) y
 * "Carga Horaria (calculada)" de CU 17 P2 (suma de las materias del año elegido).
 */
export function iniciarComision() {
    // Cada combo recarga P1 con los valores de todos los combos de la pantalla (por su "name").
    const combos = [...document.querySelectorAll('[data-recargar-carrera]')];
    combos.forEach((combo) => {
        combo.addEventListener('change', () => {
            const params = Object.fromEntries(combos.map((c) => [c.name, c.value]));
            window.location.href = `${combo.dataset.url}?${new URLSearchParams(params)}`;
        });
    });

    const anio = document.querySelector('[data-comision-anio]');
    const carga = document.querySelector('[data-comision-carga]');
    if (!anio || !carga) {
        return;
    }

    const cargas = JSON.parse(anio.dataset.cargas);
    anio.addEventListener('change', () => {
        carga.value = anio.value ? `${cargas[anio.value] ?? 0} hs` : '';
    });
}
