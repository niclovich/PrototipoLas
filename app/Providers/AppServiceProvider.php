<?php

namespace App\Providers;

use App\Contracts\VerificadorLegajoAlumno;
use App\Enums\RolSistema;
use App\Models\User;
use App\Services\Auth\VerificadorLegajoActivo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    public array $bindings = [
        VerificadorLegajoAlumno::class => VerificadorLegajoActivo::class,
    ];

    public function boot(): void
    {
        Gate::before(fn (User $usuario) => $usuario->tieneRol(RolSistema::SuperAdmin) ? true : null);

        Model::preventLazyLoading(! $this->app->isProduction());

        Carbon::setLocale(config('app.locale'));
    }
}
