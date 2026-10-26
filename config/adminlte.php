<?php

use JeroenNoten\LaravelAdminLte\Menu\Filters;

/*
| Configuración de AdminLTE 3 (jeroennoten/laravel-adminlte 3.16). Plan general §5.5.
| Las claves omitidas usan el valor por defecto del paquete.
*/

$asset = fn (string $ruta) => ['type' => str_ends_with($ruta, '.css') ? 'css' : 'js', 'asset' => true, 'location' => 'vendor/'.$ruta];

return [

    // ---------------------------------------------------------------- Título y logo
    'title' => env('APP_NAME', 'Sistema LAS'),
    'title_prefix' => '',
    'title_postfix' => ' | '.env('APP_NAME', 'Sistema LAS'),

    'use_ico_only' => false,
    'use_full_favicon' => false,

    'google_fonts' => ['allowed' => false],

    'logo' => '<b>LAS</b> <span class="las-logo-instituto">'.e(env('LAS_INSTITUTO_NOMBRE', 'Instituto')).'</span>',
    'logo_img' => 'img/logo-las.svg',
    'logo_img_class' => 'brand-image elevation-2',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => env('LAS_INSTITUTO_NOMBRE', 'Instituto'),

    'auth_logo' => ['enabled' => false],

    'preloader' => ['enabled' => false],

    // ---------------------------------------------------------------- Menú de usuario
    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-las',
    'usermenu_image' => false,
    'usermenu_desc' => true,
    'usermenu_profile_url' => false,

    // ---------------------------------------------------------------- Layout
    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    'classes_auth_card' => 'card-outline card-las',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-las',

    'classes_body' => '',
    'classes_brand' => 'bg-las',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => 'container-fluid',
    'classes_content' => 'container-fluid',
    'classes_sidebar' => 'sidebar-dark-las elevation-4',
    'classes_sidebar_nav' => 'nav-child-indent',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    'right_sidebar' => false,

    // ---------------------------------------------------------------- URLs (Fortify)
    'use_route_url' => false,
    'dashboard_url' => 'panel',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => false,
    'password_reset_url' => 'forgot-password',
    'password_email_url' => 'forgot-password',
    'profile_url' => false,
    'disable_darkmode_routes' => true,

    // Vite solo para el CSS/JS propio (se agrega desde layouts.panel y layouts.auth).
    'laravel_asset_bundling' => false,

    // ---------------------------------------------------------------- Menú lateral (plan §2.3)
    'menu' => [
        ['text' => 'Inicio', 'route' => 'panel.inicio', 'icon' => 'fas fa-fw fa-home'],

        ['header' => 'ACADÉMICO', 'can' => ['listar_carrera', 'listar_materia', 'listar_plan_estudio', 'listar_comision']],
        ['text' => 'Carreras', 'route' => 'panel.carreras.index', 'icon' => 'fas fa-fw fa-graduation-cap', 'can' => 'listar_carrera'],
        ['text' => 'Materias', 'route' => 'panel.materias.index', 'icon' => 'fas fa-fw fa-book', 'can' => 'listar_materia'],
        ['text' => 'Planes de Estudio', 'route' => 'panel.planes.index', 'icon' => 'fas fa-fw fa-sitemap', 'can' => 'listar_plan_estudio'],
        ['text' => 'Comisiones', 'route' => 'panel.comisiones.index', 'icon' => 'fas fa-fw fa-users', 'can' => 'listar_comision'],

        ['header' => 'ALUMNOS', 'can' => ['crear_preinscripcion', 'listar_alumno']],
        ['text' => 'Preinscripción', 'route' => 'panel.preinscripcion.create', 'icon' => 'fas fa-fw fa-user-plus', 'can' => 'crear_preinscripcion'],
        ['text' => 'Gestión de Alumnos', 'route' => 'panel.alumnos.index', 'icon' => 'fas fa-fw fa-user-graduate', 'can' => 'listar_alumno'],

        ['header' => 'EXÁMENES', 'can' => 'listar_acta_examen'],
        ['text' => 'Actas de Examen', 'route' => 'panel.actas.index', 'icon' => 'fas fa-fw fa-file-signature', 'can' => 'listar_acta_examen'],

        ['header' => 'FINANZAS', 'can' => ['listar_cuota', 'listar_caja']],
        ['text' => 'Cuotas por Carrera', 'route' => 'panel.cuotas.index', 'icon' => 'fas fa-fw fa-file-invoice-dollar', 'can' => 'listar_cuota'],
        ['text' => 'Cajas', 'route' => 'panel.cajas.index', 'icon' => 'fas fa-fw fa-cash-register', 'can' => 'listar_caja'],

        ['header' => 'PERSONAL', 'can' => 'listar_personal'],
        ['text' => 'Gestión de Personal', 'route' => 'panel.personal.index', 'icon' => 'fas fa-fw fa-id-badge', 'can' => 'listar_personal'],

        ['header' => 'ADMINISTRACIÓN', 'can' => ['listar_usuario', 'listar_rol', 'listar_catalogo', 'listar_auditoria']],
        ['text' => 'Usuarios', 'route' => 'panel.usuarios.index', 'icon' => 'fas fa-fw fa-user-cog', 'can' => 'listar_usuario'],
        ['text' => 'Roles y Permisos', 'route' => 'panel.roles.index', 'icon' => 'fas fa-fw fa-user-shield', 'can' => 'listar_rol'],
        ['text' => 'Catálogos', 'route' => 'panel.catalogos.index', 'icon' => 'fas fa-fw fa-list', 'can' => 'listar_catalogo'],
        ['text' => 'Auditoría', 'route' => 'panel.auditoria.index', 'icon' => 'fas fa-fw fa-history', 'can' => 'listar_auditoria'],
    ],

    'filters' => [
        Filters\GateFilter::class,
        Filters\HrefFilter::class,
        Filters\SearchFilter::class,
        Filters\ActiveFilter::class,
        Filters\ClassesFilter::class,
        Filters\LangFilter::class,
        Filters\DataFilter::class,
    ],

    // ---------------------------------------------------------------- Plugins (archivos locales en public/vendor)
    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                $asset('datatables/js/jquery.dataTables.min.js'),
                $asset('datatables/js/dataTables.bootstrap4.min.js'),
                $asset('datatables/css/dataTables.bootstrap4.min.css'),
                $asset('datatables-plugins/responsive/js/dataTables.responsive.min.js'),
                $asset('datatables-plugins/responsive/js/responsive.bootstrap4.min.js'),
                $asset('datatables-plugins/responsive/css/responsive.bootstrap4.min.css'),
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                $asset('select2/js/select2.full.min.js'),
                $asset('select2/js/i18n/es.js'),
                $asset('select2/css/select2.min.css'),
                $asset('select2-bootstrap4-theme/select2-bootstrap4.min.css'),
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                $asset('sweetalert2/sweetalert2.min.js'),
                $asset('sweetalert2-theme-bootstrap-4/bootstrap-4.min.css'),
            ],
        ],
        'TempusDominusBs4' => [
            'active' => true,
            'files' => [
                $asset('moment/moment.min.js'),
                $asset('moment/locale/es.js'),
                $asset('tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js'),
                $asset('tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css'),
            ],
        ],
        'Inputmask' => [
            'active' => true,
            'files' => [
                $asset('inputmask/jquery.inputmask.min.js'),
            ],
        ],
        'BsCustomFileInput' => [
            'active' => true,
            'files' => [
                $asset('bs-custom-file-input/bs-custom-file-input.min.js'),
            ],
        ],
        'IcheckBootstrap' => [
            'active' => true,
            'files' => [
                $asset('icheck-bootstrap/icheck-bootstrap.min.css'),
            ],
        ],
    ],

    'iframe' => [
        'default_tab' => ['url' => null, 'title' => null],
        'buttons' => [
            'close' => true, 'close_all' => true, 'close_all_other' => true,
            'scroll_left' => true, 'scroll_right' => true, 'fullscreen' => true,
        ],
        'options' => ['loading_screen' => 1000, 'auto_show_new_tab' => true, 'use_navbar_items' => true],
    ],

    'livewire' => false,
];
