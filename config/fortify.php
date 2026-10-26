<?php

use Laravel\Fortify\Features;

return [

    'guard' => 'web',

    'passwords' => 'users',

    /*
    | Login con CORREO ELECTRÓNICO (users.email = correoUsuario). El DNI no sirve para entrar.
    */
    'username' => 'email',

    'email' => 'email',

    'lowercase_usernames' => true,

    'home' => '/panel',

    'prefix' => '',

    'domain' => null,

    'middleware' => ['web'],

    /*
    | login = null: se usa el LoginRateLimiter propio de Fortify (5 intentos por minuto
    | por correo+IP), que devuelve el error en el formulario en lugar de una página 429.
    */
    'limiters' => [
        'login' => null,
        'verification' => '6,1',
    ],

    'views' => true,

    /*
    | Sin registro público, sin perfil, sin 2FA ni passkeys.
    */
    'features' => [
        Features::resetPasswords(),
        Features::emailVerification(),
    ],

];
