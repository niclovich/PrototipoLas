<?php

namespace App\Http\Controllers\Panel\Administracion;

use App\DataTables\RolesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administracion\GuardarRolRequest;
use App\Models\Rol;
use App\Services\Usuarios\RolService;
use App\Support\Permisos;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function __construct(private readonly RolService $roles) {}

    public function index(): View
    {
        return view('panel.administracion.roles.index');
    }

    public function datatable(Request $request, RolesDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }

    public function create(): View
    {
        return view('panel.administracion.roles.create', ['rol' => new Rol, 'gruposPermisos' => Permisos::agrupados()]);
    }

    public function store(GuardarRolRequest $request): RedirectResponse
    {
        $this->roles->crear($request->datosRol(), $request->permisos());

        return redirect()->route('panel.roles.index')->with('success', 'Rol creado.');
    }

    public function edit(Rol $rol): View
    {
        return view('panel.administracion.roles.edit', [
            'rol' => $rol->load('permissions'),
            'gruposPermisos' => Permisos::agrupados(),
        ]);
    }

    public function update(GuardarRolRequest $request, Rol $rol): RedirectResponse
    {
        $this->roles->actualizar($rol, $request->datosRol(), $request->permisos());

        return redirect()->route('panel.roles.index')->with('success', 'Rol modificado.');
    }

    public function destroy(Rol $rol): RedirectResponse
    {
        $this->roles->darDeBaja($rol);

        return back()->with('success', 'Rol dado de baja.');
    }

    public function reactivar(Rol $rol): RedirectResponse
    {
        $this->roles->reactivar($rol);

        return back()->with('success', 'Rol reactivado.');
    }
}
