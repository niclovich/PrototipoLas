<?php

namespace App\Http\Controllers\Panel\Alumnos;

use App\DataTables\AlumnoDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** P1 Gestión de Alumnos (Fig. 72), compartida por CU2, CU3, CU5 y CU7. */
class AlumnoController extends Controller
{
    /** Clave de sesión del aviso que P1 muestra en un modal (p. ej. CU7 alt. 2.2 y 6.2). */
    public const AVISO = 'aviso_alumnos';

    public const AVISO_TITULO = 'aviso_alumnos_titulo';

    public const AVISO_LISTA = 'aviso_alumnos_lista';

    /**
     * Vuelve a P1 con un aviso en el modal (título del CU, mensaje exacto de la spec y renglones opcionales).
     *
     * @param  list<string>  $lista
     */
    public static function avisar(string $titulo, string $mensaje, array $lista = []): RedirectResponse
    {
        return redirect()->route('panel.alumnos.index')
            ->with(self::AVISO, $mensaje)->with(self::AVISO_TITULO, $titulo)->with(self::AVISO_LISTA, $lista);
    }

    public function index(): View
    {
        return view('panel.alumnos.index');
    }

    public function datatable(Request $request, AlumnoDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }
}
