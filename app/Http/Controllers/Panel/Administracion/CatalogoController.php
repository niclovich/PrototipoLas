<?php

namespace App\Http\Controllers\Panel\Administracion;

use App\DataTables\CatalogoDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administracion\GuardarCatalogoRequest;
use App\Services\Catalogos\CatalogoService;
use App\Support\DefinicionCatalogo;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * ABM genérico de catálogos (plan §6 E1). {catalogo} = clave de config/catalogos.php,
 * {registro} = registro de ese catálogo, incluidos los dados de baja (routes/panel.php).
 */
class CatalogoController extends Controller
{
    public function __construct(private readonly CatalogoService $catalogos) {}

    public function listado(): View
    {
        return view('panel.administracion.catalogos.listado', ['catalogos' => DefinicionCatalogo::todos()]);
    }

    public function index(DefinicionCatalogo $catalogo): View
    {
        return view('panel.administracion.catalogos.index', ['catalogo' => $catalogo]);
    }

    public function datatable(Request $request, CatalogoDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }

    public function create(DefinicionCatalogo $catalogo): View
    {
        return view('panel.administracion.catalogos.create', ['catalogo' => $catalogo, 'registro' => $catalogo->nuevo()]);
    }

    public function store(GuardarCatalogoRequest $request, DefinicionCatalogo $catalogo): RedirectResponse
    {
        $this->catalogos->crear($catalogo, $request->validated());

        return redirect()->route('panel.catalogos.registros.index', $catalogo->clave)->with('success', "{$catalogo->singular} registrado/a.");
    }

    public function edit(DefinicionCatalogo $catalogo, Model $registro): View
    {
        return view('panel.administracion.catalogos.edit', ['catalogo' => $catalogo, 'registro' => $registro]);
    }

    public function update(GuardarCatalogoRequest $request, DefinicionCatalogo $catalogo, Model $registro): RedirectResponse
    {
        $this->catalogos->actualizar($registro, $request->validated());

        return redirect()->route('panel.catalogos.registros.index', $catalogo->clave)->with('success', "{$catalogo->singular} modificado/a.");
    }

    public function destroy(DefinicionCatalogo $catalogo, Model $registro): RedirectResponse
    {
        $this->catalogos->darDeBaja($registro);

        return back()->with('success', "{$catalogo->singular} dado/a de baja.");
    }

    public function restaurar(DefinicionCatalogo $catalogo, Model $registro): RedirectResponse
    {
        $this->catalogos->restaurar($registro);

        return back()->with('success', "{$catalogo->singular} reactivado/a.");
    }
}
