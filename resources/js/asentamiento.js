/*
 * CU6 P3 Formulario Registrar Nota: la Condición Final (solo lectura) se calcula mientras se escribe la nota
 * (Aprobado si nota >= 4; el servidor vuelve a calcularla al guardar).
 */
export function iniciarAsentamiento() {
    const nota = document.querySelector('[data-nota-examen]');
    const condicion = document.querySelector('[data-condicion-final]');
    if (!nota || !condicion) {
        return;
    }

    const minima = Number(nota.dataset.notaAprobacion);
    const actualizar = () => {
        const valor = Number(nota.value.replace(',', '.').trim());
        const valida = nota.value.trim() !== '' && Number.isFinite(valor) && valor >= 1 && valor <= 10;
        condicion.value = valida ? (valor >= minima ? 'Aprobado' : 'Desaprobado') : '';
    };

    nota.addEventListener('input', actualizar);
    actualizar();
}
