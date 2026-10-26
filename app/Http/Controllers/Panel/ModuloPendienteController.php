<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Pantalla provisoria de los módulos del menú que todavía no se implementaron.
 * La ruta ya exige el permiso definitivo; cada etapa la reemplaza por su controller.
 */
class ModuloPendienteController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('panel.modulo-pendiente', [
            'titulo' => collect(config('adminlte.menu'))
                ->firstWhere('route', $request->route()->getName())['text'] ?? 'Módulo',
        ]);
    }
}
