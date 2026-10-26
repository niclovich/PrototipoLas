<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Services\Auth\AutenticacionService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateUsing(
            fn (Request $request) => $this->app->make(AutenticacionService::class)
                ->autenticar((string) $request->input('email'), (string) $request->input('password'))
        );

        Fortify::loginView(fn () => view('auth.login'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.olvide-contrasenia'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.restablecer-contrasenia', ['request' => $request]));
        Fortify::verifyEmailView(fn () => view('auth.verificar-correo'));
        Fortify::confirmPasswordView(fn () => view('auth.confirmar-contrasenia'));
    }
}
