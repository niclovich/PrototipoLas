<?php

return [

    /*
    | Parámetros propios del Sistema LAS (se leen del .env; nada de valores mágicos en el código).
    */

    'instituto_nombre' => env('LAS_INSTITUTO_NOMBRE', 'Instituto Superior del Milagro'),

    'punto_venta' => env('LAS_PUNTO_VENTA', '0001'),

    'cuota_dia_vencimiento' => (int) env('LAS_CUOTA_DIA_VENCIMIENTO', 10),

    'link_alta_horas' => (int) env('LAS_LINK_ALTA_HORAS', 48),

    'legajo_inicial' => (int) env('LAS_LEGAJO_INICIAL', 10000),

    'admin' => [
        'nombre' => env('ADMIN_NOMBRE', 'Administrador'),
        'apellido' => env('ADMIN_APELLIDO', 'Sistema'),
        'dni' => env('ADMIN_DNI', '00000000'),
        'email' => env('ADMIN_EMAIL', 'admin@las.test'),
        'password' => env('ADMIN_PASSWORD'),
    ],

];
