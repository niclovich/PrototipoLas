<?php

return [

    /*
    | Parámetros propios del Sistema LAS (se leen del .env; nada de valores mágicos en el código).
    */

    'instituto_nombre' => env('LAS_INSTITUTO_NOMBRE', 'Instituto Superior del Milagro'),

    'punto_venta' => env('LAS_PUNTO_VENTA', '0001'),

    // CU7 Comprobante de Pago (Fig. 88): datos fiscales del instituto (valores del wireframe). Sin CAE (§7-6).
    'comprobante' => [
        'razon_social' => env('LAS_RAZON_SOCIAL', 'INSTITUTO SUPERIOR DEL MILAGRO S.R.L.'),
        'direccion' => env('LAS_DIRECCION', 'ALVARADO 951'),
        'condicion_iva' => env('LAS_CONDICION_IVA', 'Responsable Exento'),
        'cuit' => env('LAS_CUIT', '33709482969'),
        'ingresos_brutos' => env('LAS_INGRESOS_BRUTOS', '33709482969'),
        'inicio_actividades' => env('LAS_INICIO_ACTIVIDADES', '01/01/2006'),
        'tipo' => 'FACTURA C',
        'codigo' => '11',
    ],

    'cuota_dia_vencimiento' => (int) env('LAS_CUOTA_DIA_VENCIMIENTO', 10),

    'link_alta_horas' => (int) env('LAS_LINK_ALTA_HORAS', 48),

    'legajo_inicial' => (int) env('LAS_LEGAJO_INICIAL', 10000),

    // CU 9: P2 no tiene campo País; Provincias y Localidades son de Argentina (decisión D4 de E3).
    'pais_por_defecto' => env('LAS_PAIS_POR_DEFECTO', 'Argentina'),

    // CU 9: foto de perfil opcional, jpg/jpeg/png (decisión D5 de E3).
    'foto_max_kb' => (int) env('LAS_FOTO_MAX_KB', 2048),

    // CU1 P4: Fotocopia de DNI y Certificado de Título en PDF.
    'documento_max_kb' => (int) env('LAS_DOCUMENTO_MAX_KB', 5120),

    'admin' => [
        'nombre' => env('ADMIN_NOMBRE', 'Administrador'),
        'apellido' => env('ADMIN_APELLIDO', 'Sistema'),
        'dni' => env('ADMIN_DNI', '00000000'),
        'email' => env('ADMIN_EMAIL', 'admin@las.test'),
        'password' => env('ADMIN_PASSWORD'),
    ],

];
