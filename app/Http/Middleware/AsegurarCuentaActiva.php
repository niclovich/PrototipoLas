<?php

namespace App\Http\Middleware;

use App\Services\Auth\AutenticacionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * estadoCuenta controla el acceso (skill §2): si la cuenta se dio de baja con la sesión
 * abierta o con "Recordarme", se cierra la sesión en el siguiente pedido.
 */
class AsegurarCuentaActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario && ! $usuario->estaActivo()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => AutenticacionService::MENSAJE_NO_ACTIVO]);
        }

        return $next($request);
    }
}
