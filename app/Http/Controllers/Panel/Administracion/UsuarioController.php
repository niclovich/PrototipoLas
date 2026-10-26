<?php

namespace App\Http\Controllers\Panel\Administracion;

use App\DataTables\UsuariosDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administracion\GuardarUsuarioRequest;
use App\Models\User;
use App\Services\Usuarios\UsuarioService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Usuarios del personal y técnicos. Los usuarios Alumno no se gestionan acá (CU1 / CU 9):
 * el parámetro {usuario} solo resuelve usuarios gestionables (routes/panel.php).
 */
class UsuarioController extends Controller
{
    public function __construct(private readonly UsuarioService $usuarios) {}

    public function index(): View
    {
        return view('panel.administracion.usuarios.index');
    }

    public function datatable(Request $request, UsuariosDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }

    public function create(Request $request): View
    {
        return view('panel.administracion.usuarios.create', [
            'usuario' => new User,
            'roles' => $this->usuarios->rolesAsignables($request->user()),
        ]);
    }

    public function store(GuardarUsuarioRequest $request): RedirectResponse
    {
        $usuario = $this->usuarios->crear($request->datosUsuario(), $request->validated('rol'), $request->user());

        return redirect()->route('panel.usuarios.index')
            ->with('success', "Usuario creado. Se envió a {$usuario->email} el enlace para definir la contraseña.");
    }

    public function edit(Request $request, User $usuario): View
    {
        return view('panel.administracion.usuarios.edit', [
            'usuario' => $usuario->load('roles'),
            'roles' => $this->usuarios->rolesAsignables($request->user()),
        ]);
    }

    public function update(GuardarUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $this->usuarios->actualizar($usuario, $request->datosUsuario(), $request->validated('rol'), $request->user());

        return redirect()->route('panel.usuarios.index')->with('success', 'Usuario modificado.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        $this->usuarios->darDeBaja($usuario, $request->user());

        return back()->with('success', 'Usuario dado de baja.');
    }

    public function reactivar(Request $request, User $usuario): RedirectResponse
    {
        $this->usuarios->reactivar($usuario, $request->user());

        return back()->with('success', 'Usuario reactivado.');
    }

    public function reenviarLink(User $usuario): RedirectResponse
    {
        if ($usuario->password !== null) {
            return back()->with('warning', 'El usuario ya definió su contraseña.');
        }

        $this->usuarios->enviarLinkAlta($usuario);

        return back()->with('success', "Se reenvió el enlace a {$usuario->email}.");
    }
}
