<?php

use App\Http\Controllers\Panel\Administracion\CatalogoController;
use App\Http\Controllers\Panel\Administracion\RolController;
use App\Http\Controllers\Panel\Administracion\UsuarioController;
use App\Http\Controllers\Panel\InicioController;
use App\Http\Controllers\Panel\ModuloPendienteController;
use App\Models\User;
use App\Support\DefinicionCatalogo;
use Illuminate\Routing\Route as RutaLaravel;
use Illuminate\Support\Facades\Route;

/*
| Panel: middleware ['web', 'auth', 'verified'], prefijo 'panel', nombre 'panel.' (bootstrap/app.php).
| Cada ruta exige su permiso con can:. Lo que modifica datos va por POST/PUT/PATCH/DELETE.
*/

// {usuario} en Administración solo resuelve usuarios gestionables desde Usuarios (no Alumno: CU1 / CU 9).
Route::bind('usuario', fn (string $id) => User::query()->gestionablesDesdeUsuarios()->findOrFail($id));
Route::bind('catalogo', fn (string $clave) => DefinicionCatalogo::buscar($clave) ?? abort(404));
Route::bind('registro', fn (string $id, RutaLaravel $ruta) => $ruta->parameter('catalogo')->consulta()->withTrashed()->findOrFail($id));

Route::get('/', InicioController::class)->name('inicio');

// ---------------------------------------------------------------- Administración (base común)
Route::prefix('administracion')->group(function () {
    Route::controller(UsuarioController::class)->prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:listar_usuario');
        Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_usuario');
        Route::get('crear', 'create')->name('create')->middleware('can:crear_usuario');
        Route::post('/', 'store')->name('store')->middleware('can:crear_usuario');
        Route::get('{usuario}/editar', 'edit')->name('edit')->middleware('can:editar_usuario');
        Route::put('{usuario}', 'update')->name('update')->middleware('can:editar_usuario');
        Route::delete('{usuario}', 'destroy')->name('destroy')->middleware('can:eliminar_usuario');
        Route::patch('{usuario}/reactivar', 'reactivar')->name('reactivar')->middleware('can:eliminar_usuario');
        Route::post('{usuario}/reenviar-link', 'reenviarLink')->name('reenviar-link')->middleware('can:crear_usuario');
    });

    Route::controller(RolController::class)->prefix('roles')->name('roles.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:listar_rol');
        Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_rol');
        Route::get('crear', 'create')->name('create')->middleware('can:crear_rol');
        Route::post('/', 'store')->name('store')->middleware('can:crear_rol');
        Route::get('{rol}/editar', 'edit')->name('edit')->middleware('can:editar_rol');
        Route::put('{rol}', 'update')->name('update')->middleware('can:editar_rol');
        Route::delete('{rol}', 'destroy')->name('destroy')->middleware('can:eliminar_rol');
        Route::patch('{rol}/reactivar', 'reactivar')->name('reactivar')->middleware('can:eliminar_rol');
    });

    // ABM genérico de catálogos (config/catalogos.php). {registro} incluye los dados de baja.
    Route::controller(CatalogoController::class)->prefix('catalogos')->name('catalogos.')->group(function () {
        Route::get('/', 'listado')->name('index')->middleware('can:listar_catalogo');
        Route::name('registros.')->prefix('{catalogo}')->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:listar_catalogo');
            Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_catalogo');
            Route::get('crear', 'create')->name('create')->middleware('can:crear_catalogo');
            Route::post('/', 'store')->name('store')->middleware('can:crear_catalogo');
            Route::get('{registro}/editar', 'edit')->name('edit')->middleware('can:editar_catalogo');
            Route::put('{registro}', 'update')->name('update')->middleware('can:editar_catalogo');
            Route::delete('{registro}', 'destroy')->name('destroy')->middleware('can:eliminar_catalogo');
            Route::patch('{registro}/reactivar', 'restaurar')->name('reactivar')->middleware('can:eliminar_catalogo');
        });
    });
});

// ---------------------------------------------------------------- Módulos pendientes
// Pantallas de entrada del menú (plan §2.3) que se implementan en las etapas E1–E19.
// Cada etapa reemplaza su línea por el controller real, con el mismo nombre de ruta y permiso.
foreach ([
    'carreras' => ['academico/carreras', 'listar_carrera', 'index'],
    'materias' => ['academico/materias', 'listar_materia', 'index'],
    'planes' => ['academico/planes', 'listar_plan_estudio', 'index'],
    'comisiones' => ['academico/comisiones', 'listar_comision', 'index'],
    'preinscripcion' => ['alumnos/preinscripcion', 'crear_preinscripcion', 'create'],
    'alumnos' => ['alumnos/gestion', 'listar_alumno', 'index'],
    'actas' => ['examenes/actas', 'listar_acta_examen', 'index'],
    'cuotas' => ['finanzas/cuotas', 'listar_cuota', 'index'],
    'cajas' => ['finanzas/cajas', 'listar_caja', 'index'],
    'personal' => ['personal', 'listar_personal', 'index'],
    'auditoria' => ['administracion/auditoria', 'listar_auditoria', 'index'],
] as $modulo => [$uri, $permiso, $accion]) {
    Route::get($uri, ModuloPendienteController::class)
        ->name("{$modulo}.{$accion}")
        ->middleware('can:'.$permiso);
}
