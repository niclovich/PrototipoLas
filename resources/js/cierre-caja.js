/*
 * CU 36 P2 Registrar Cierre de Caja (pasos 9–11): al cargar cada Monto Contado se calcula su Diferencia
 * (Contado − Esperado) y la Diferencia Total. El servidor vuelve a calcular todo.
 */
import { formatearMonto } from './mascaras';
import { leerMonto } from './pago-cuotas';

export function iniciarCierreCaja() {
    const form = document.querySelector('[data-cierre-caja]');
    if (!form) {
        return;
    }

    const filas = [...form.querySelectorAll('[data-fila-medio]')];
    const total = form.querySelector('[data-diferencia-total]');

    function recalcular() {
        let suma = 0;
        filas.forEach((fila) => {
            const contado = fila.querySelector('[data-contado]');
            const salida = fila.querySelector('[data-diferencia]');
            if (contado.value.trim() === '') {
                salida.textContent = '—';

                return;
            }
            const diferencia = Math.round((leerMonto(contado.value) - Number(fila.dataset.esperado)) * 100) / 100;
            suma += diferencia;
            salida.textContent = formatearMonto(diferencia);
        });
        total.value = formatearMonto(Math.round(suma * 100) / 100);
    }

    filas.forEach((fila) => fila.querySelector('[data-contado]').addEventListener('input', recalcular));
    recalcular();
}
