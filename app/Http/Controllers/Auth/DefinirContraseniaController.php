<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\DefinirContraseniaRequest;
use App\Models\User;
use App\Services\Usuarios\UsuarioService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Alta por link firmado (48 h, un solo uso): el usuario define su contraseña.
 */
class DefinirContraseniaController extends Controller
{
    public function __construct(private readonly UsuarioService $usuarios) {}

    public function create(Request $request, User $user, string $hash): View
    {
        $this->verificarLink($user, $hash);

        return view('auth.definir-contrasenia', ['usuario' => $user, 'accion' => $request->fullUrl()]);
    }

    public function store(DefinirContraseniaRequest $request, User $user, string $hash): RedirectResponse
    {
        $this->verificarLink($user, $hash);

        $this->usuarios->definirContrasenia($user, $request->validated('password'));

        return redirect()->route('login')
            ->with('success', 'Su contraseña fue definida. Ya puede ingresar con su correo electrónico.');
    }

    private function verificarLink(User $user, string $hash): void
    {
        abort_unless($this->usuarios->linkVigente($user, $hash), 403, 'Este enlace ya fue utilizado o no es válido.');
    }
}
