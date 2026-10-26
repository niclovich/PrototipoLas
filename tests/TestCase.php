<?php

namespace Tests;

use App\Enums\RolSistema;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** @param  array<string, mixed>  $atributos */
    protected function crearUsuario(?RolSistema $rol = null, array $atributos = []): User
    {
        $usuario = User::factory()->create($atributos);

        if ($rol) {
            $usuario->assignRole($rol->value);
        }

        return $usuario;
    }

    protected function superAdmin(): User
    {
        return $this->crearUsuario(RolSistema::SuperAdmin);
    }
}
