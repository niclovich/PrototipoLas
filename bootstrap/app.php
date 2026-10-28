<?php

use App\Http\Middleware\AsegurarCuentaActiva;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'verified'])
                ->prefix('panel')
                ->name('panel.')
                ->group(base_path('routes/panel.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'cuenta.activa' => AsegurarCuentaActiva::class,
        ]);

        // estadoCuenta controla el acceso en TODAS las rutas web con sesión: panel y rutas de Fortify
        // (email/verify*, user/confirm-password…). Para invitados no hace nada.
        $middleware->appendToGroup('web', AsegurarCuentaActiva::class);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('panel.inicio'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Las páginas 403, 404, 419, 429, 500 y 503 están en resources/views/errors (español, AdminLTE).
        $exceptions->render(function (InvalidSignatureException $e, Request $request) {
            if (! $request->expectsJson()) {
                return response()->view('errors.403', ['mensaje' => 'El enlace venció o no es válido. Solicite uno nuevo al administrador.'], 403);
            }
        });

        // Mensajes propios de abort(403, '...') (p. ej. link de alta ya utilizado).
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 403 && ! $request->expectsJson() && ! in_array($e->getMessage(), ['', 'This action is unauthorized.'], true)) {
                return response()->view('errors.403', ['mensaje' => $e->getMessage()], 403);
            }
        });
    })->create();
