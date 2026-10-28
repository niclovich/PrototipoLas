/*
 * CU7 P2 Registrar Pago de Cuotas (pasos 8–17, decisión §7-5: un solo medio de pago por Pago):
 *  - "Cobrar" recalcula Monto Original, Bonificacion (Beca: porcentaje), Total a Pagar y Saldo Pendiente.
 *  - "Agregar" fija el medio y el importe (reemplaza al anterior) y actualiza "Importe en <medio>".
 *  - "Registrar Pago" se habilita con al menos una cuota y Saldo Pendiente 0. El servidor vuelve a calcular todo.
 */
import { formatearMonto } from './mascaras';

const redondear = (valor) => Math.round(valor * 100) / 100;

export function leerMonto(texto) {
    const limpio = String(texto ?? '').replace(/[^\d,.-]/g, '');
    const normalizado = limpio.includes(',') ? limpio.replace(/\./g, '').replace(',', '.') : limpio;

    return Number(normalizado) || 0;
}

export function iniciarPagoCuotas() {
    const form = document.querySelector('[data-pago-cuotas]');
    if (!form) {
        return;
    }

    const porcentaje = Number(form.dataset.porcentajeBeca) || 0;
    const casillas = [...form.querySelectorAll('input[name="cuotas[]"]')];
    const resumen = (clave) => form.querySelector(`[data-resumen="${clave}"]`);
    const forma = form.querySelector('[data-forma-pago]');
    const monto = form.querySelector('[data-monto-forma-pago]');
    const medio = form.querySelector('[data-medio-agregado]');
    const importe = form.querySelector('[data-importe]');
    const importeTexto = form.querySelector('[data-importe-texto]');
    const importeLabel = form.querySelector('[data-importe-label]');
    const registrar = form.querySelector('[data-registrar-pago]');

    function recalcular() {
        const marcadas = casillas.filter((c) => c.checked).map((c) => Number(c.dataset.monto));
        const original = redondear(marcadas.reduce((a, b) => a + b, 0));
        const bonificacion = redondear(marcadas.reduce((a, m) => a + redondear((m * porcentaje) / 100), 0));
        const total = redondear(original - bonificacion);
        const saldo = redondear(total - (Number(importe.value) || 0));

        resumen('original').value = formatearMonto(original);
        resumen('bonificacion').value = bonificacion > 0 ? `-${formatearMonto(bonificacion)}` : formatearMonto(0);
        resumen('total').value = formatearMonto(total);
        resumen('saldo').value = formatearMonto(saldo);
        registrar.disabled = marcadas.length === 0 || saldo !== 0 || !medio.value;
    }

    form.querySelector('[data-agregar]').addEventListener('click', () => {
        const valor = redondear(leerMonto(monto.value));
        medio.value = forma.value;
        importe.value = valor;
        importeTexto.value = formatearMonto(valor);
        importeLabel.textContent = `Importe en ${forma.options[forma.selectedIndex].text}`;
        monto.value = '0';
        recalcular();
    });

    casillas.forEach((c) => c.addEventListener('change', recalcular));
    if (importe.value) {
        importeTexto.value = formatearMonto(Number(importe.value));
    }
    recalcular();
}
