<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Pantallas de Confirmación por sesión (redirect después del POST): el controller guarda los datos
 * y la ruta GET de confirmación los lee una sola vez. Sin datos (recarga o acceso directo) vuelve a P1.
 */
trait ConPantallaConfirmacion
{
    private const CLAVE_CONFIRMACION = 'confirmacion';

    /** @param  array<string, mixed>  $datos */
    protected function confirmar(string $rutaConfirmacion, array $datos): RedirectResponse
    {
        return redirect()->route($rutaConfirmacion)->with(self::CLAVE_CONFIRMACION, $datos);
    }

    /** @return array<string, mixed>|null */
    protected function datosConfirmacion(Request $request): ?array
    {
        $datos = $request->session()->get(self::CLAVE_CONFIRMACION);

        return is_array($datos) ? $datos : null;
    }
}
