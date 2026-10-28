<?php

use App\Http\Controllers\Panel\Academico\CarreraController;
use App\Http\Controllers\Panel\Academico\ComisionController;
use App\Http\Controllers\Panel\Academico\CorrelatividadController;
use App\Http\Controllers\Panel\Academico\DetallePlanEstudioController;
use App\Http\Controllers\Panel\Academico\MateriaController;
use App\Http\Controllers\Panel\Academico\PlanEstudioController;
use App\Http\Controllers\Panel\Administracion\AuditoriaController;
use App\Http\Controllers\Panel\Administracion\CatalogoController;
use App\Http\Controllers\Panel\Administracion\RolController;
use App\Http\Controllers\Panel\Administracion\UsuarioController;
use App\Http\Controllers\Panel\Alumnos\AlumnoController;
use App\Http\Controllers\Panel\Alumnos\InscripcionComisionController;
use App\Http\Controllers\Panel\Alumnos\InscripcionController;
use App\Http\Controllers\Panel\Alumnos\InscripcionExamenController;
use App\Http\Controllers\Panel\Alumnos\PreinscripcionController;
use App\Http\Controllers\Panel\Alumnos\RegularidadController;
use App\Http\Controllers\Panel\Catalogos\LocalidadesPorProvinciaController;
use App\Http\Controllers\Panel\Examenes\ActaExamenController;
use App\Http\Controllers\Panel\Examenes\AsentamientoActaController;
use App\Http\Controllers\Panel\Finanzas\CajaController;
use App\Http\Controllers\Panel\Finanzas\CuotaController;
use App\Http\Controllers\Panel\Finanzas\PagoCuotaController;
use App\Http\Controllers\Panel\InicioController;
use App\Http\Controllers\Panel\Personal\PersonalController;
use App\Models\DetallePlanEstudio;
use App\Models\PlanEstudio;
use App\Models\Provincia;
use App\Models\User;
use App\Support\DefinicionCatalogo;
use Illuminate\Routing\Route as RutaLaravel;
use Illuminate\Support\Facades\Route;
use Spatie\Activitylog\Models\Activity;

/*
| Panel: middleware ['web', 'auth', 'verified'], prefijo 'panel', nombre 'panel.' (bootstrap/app.php).
| Cada ruta exige su permiso con can:. Lo que modifica datos va por POST/PUT/PATCH/DELETE.
*/

// {usuario} en Administración solo resuelve usuarios gestionables desde Usuarios (no Alumno: CU1 / CU 9).
Route::bind('usuario', fn (string $id) => User::query()->gestionablesDesdeUsuarios()->findOrFail($id));
// {personal}: usuario del Personal con cuenta Activo (CU 26 y 27 solo listan y operan sobre esos).
Route::bind('personal', fn (string $id) => User::query()->personalActivo()->findOrFail($id));
Route::bind('provincia', fn (string $id) => Provincia::query()->findOrFail($id));
// {plan}: Plan de Estudio "Activo" de una carrera "Activo"/"Pendiente" (CU 6). {detalle}: materia "Activo" de ese plan (CU 7, CU 8).
Route::bind('plan', fn (string $id) => PlanEstudio::query()->activos()
    ->whereHas('carrera', fn ($carrera) => $carrera->gestionablesEnPlan())->findOrFail($id));
Route::bind('detalle', fn (string $id) => DetallePlanEstudio::query()->vigentesDePlanActivo()->findOrFail($id));
Route::bind('catalogo', fn (string $clave) => DefinicionCatalogo::buscar($clave) ?? abort(404));
Route::bind('actividad', fn (string $id) => Activity::query()->findOrFail($id));
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

    // Auditoría (base común): activity_log.
    Route::controller(AuditoriaController::class)->prefix('auditoria')->name('auditoria.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:listar_auditoria');
        Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_auditoria');
        Route::get('{actividad}', 'show')->name('show')->middleware('can:ver_auditoria');
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

// ---------------------------------------------------------------- Académico: Carreras (CU 3, 4)
Route::controller(CarreraController::class)->prefix('academico/carreras')->name('carreras.')->group(function () {
    Route::get('/', 'index')->name('index')->middleware('can:listar_carrera');
    Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_carrera');
    Route::get('confirmacion', 'confirmacion')->name('confirmacion')->middleware('can:listar_carrera');
    Route::get('registrar', 'create')->name('create')->middleware('can:crear_carrera');
    Route::post('/', 'store')->name('store')->middleware('can:crear_carrera');
    Route::get('{carrera}/modificar', 'edit')->name('edit')->middleware('can:editar_carrera');
    Route::put('{carrera}', 'update')->name('update')->middleware('can:editar_carrera');
});

// ---------------------------------------------------------------- Académico: Materias (CU 1, 2)
Route::controller(MateriaController::class)->prefix('academico/materias')->name('materias.')->group(function () {
    Route::get('/', 'index')->name('index')->middleware('can:listar_materia');
    Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_materia');
    Route::get('confirmacion', 'confirmacion')->name('confirmacion')->middleware('can:listar_materia');
    Route::get('registrar', 'create')->name('create')->middleware('can:crear_materia');
    Route::post('/', 'store')->name('store')->middleware('can:crear_materia');
    Route::get('{materia}/modificar', 'edit')->name('edit')->middleware('can:editar_materia');
    Route::put('{materia}', 'update')->name('update')->middleware('can:editar_materia');
});

// ---------------------------------------------------------------- Académico: Planes de Estudio (CU 5, 6, 7, 8)
Route::prefix('academico/planes')->name('planes.')->group(function () {
    Route::controller(PlanEstudioController::class)->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:listar_plan_estudio');
        Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_plan_estudio');
        Route::get('confirmacion', 'confirmacion')->name('confirmacion')->middleware('can:listar_plan_estudio');
        Route::get('carreras/{carrera}/registrar', 'create')->name('create')->middleware('can:crear_plan_estudio');
        Route::post('carreras/{carrera}', 'store')->name('store')->middleware('can:crear_plan_estudio');
    });
    Route::controller(DetallePlanEstudioController::class)->name('materias.')->group(function () {
        Route::get('{plan}/materias/registrar', 'create')->name('create')->middleware('can:crear_materia_plan');
        Route::post('{plan}/materias', 'store')->name('store')->middleware('can:crear_materia_plan');
        Route::post('materias/{detalle}/baja', 'registrarBaja')->name('baja')->middleware('can:eliminar_materia_plan');
    });
    Route::controller(CorrelatividadController::class)->name('correlatividades.')->group(function () {
        Route::get('materias/{detalle}/correlatividades', 'create')->name('create')->middleware('can:crear_correlativa');
        Route::post('materias/{detalle}/correlatividades', 'store')->name('store')->middleware('can:crear_correlativa');
    });
});

// ---------------------------------------------------------------- Académico: Comisiones (CU 17)
Route::controller(ComisionController::class)->prefix('academico/comisiones')->name('comisiones.')->group(function () {
    Route::get('/', 'index')->name('index')->middleware('can:listar_comision');
    Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_comision');
    Route::get('confirmacion', 'confirmacion')->name('confirmacion')->middleware('can:listar_comision');
    Route::get('carreras/{carrera}/registrar', 'create')->name('create')->middleware('can:crear_comision');
    Route::post('carreras/{carrera}', 'store')->name('store')->middleware('can:crear_comision');
});

// ---------------------------------------------------------------- Exámenes: Actas de Examen (CU 21, 22)
Route::controller(ActaExamenController::class)->prefix('examenes/actas')->name('actas.')->group(function () {
    Route::get('/', 'index')->name('index')->middleware('can:listar_acta_examen');
    Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_acta_examen');
    Route::get('confirmacion', 'confirmacion')->name('confirmacion')->middleware('can:listar_acta_examen');
    Route::get('carreras/{carrera}/registrar', 'create')->name('create')->middleware('can:crear_acta_examen');
    Route::post('carreras/{carrera}', 'store')->name('store')->middleware('can:crear_acta_examen');
    Route::get('{acta}/modificar', 'edit')->name('edit')->middleware('can:editar_acta_examen');
    Route::put('{acta}', 'update')->name('update')->middleware('can:editar_acta_examen');
});

// ---------------------------------------------------------------- Finanzas: Cuotas por Carrera (CU 33, 34)
Route::controller(CuotaController::class)->prefix('finanzas/cuotas')->name('cuotas.')->group(function () {
    Route::get('/', 'index')->name('index')->middleware('can:listar_cuota');
    Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_cuota');
    Route::get('confirmacion', 'confirmacion')->name('confirmacion')->middleware('can:listar_cuota');
    Route::get('carreras/{carrera}/registrar', 'create')->name('create')->middleware('can:crear_cuota');
    Route::post('carreras/{carrera}', 'store')->name('store')->middleware('can:crear_cuota');
    Route::get('{cuota}/modificar', 'edit')->name('edit')->middleware('can:editar_cuota');
    Route::put('{cuota}', 'update')->name('update')->middleware('can:editar_cuota');
});

// ---------------------------------------------------------------- Alumnos: Preinscripción (CU1, con P2/P3 del CU 9)
Route::controller(PreinscripcionController::class)->prefix('alumnos/preinscripcion')->name('preinscripcion.')->group(function () {
    Route::get('/', 'create')->name('create')->middleware('can:crear_preinscripcion');
    Route::post('buscar', 'buscar')->name('buscar')->middleware('can:crear_preinscripcion');
    Route::get('datos-personales', 'datosPersonales')->name('datos-personales')->middleware('can:crear_usuario_alumno');
    Route::post('datos-personales', 'guardarDatosPersonales')->name('datos-personales.store')->middleware('can:crear_usuario_alumno');
    Route::get('localidades/{provincia}', LocalidadesPorProvinciaController::class)->name('localidades')->middleware('can:crear_usuario_alumno');
    Route::get('encuesta', 'encuesta')->name('encuesta')->middleware('can:crear_usuario_alumno');
    Route::post('encuesta', 'guardarEncuesta')->name('encuesta.store')->middleware('can:crear_usuario_alumno');
    Route::get('carrera', 'carrera')->name('carrera')->middleware('can:crear_preinscripcion');
    Route::post('carrera', 'guardarCarrera')->name('carrera.store')->middleware('can:crear_preinscripcion');
    Route::get('confirmar', 'confirmar')->name('confirmar')->middleware('can:crear_preinscripcion');
    Route::post('/', 'store')->name('store')->middleware('can:crear_preinscripcion');
});

// ---------------------------------------------------------------- Alumnos: Gestión de Alumnos (P1 de CU2, CU3, CU5 y CU7)
Route::controller(AlumnoController::class)->prefix('alumnos/gestion')->name('alumnos.')->group(function () {
    Route::get('/', 'index')->name('index')->middleware('can:listar_alumno');
    Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_alumno');
});

// ---------------------------------------------------------------- Alumnos: Inscripción (CU2)
Route::controller(InscripcionController::class)->prefix('alumnos/inscripcion')->name('inscripciones.')->group(function () {
    Route::get('confirmacion', 'confirmacion')->name('confirmacion')->middleware('can:crear_inscripcion');
    Route::get('{alumno}', 'create')->name('create')->middleware('can:crear_inscripcion');
    Route::post('{alumno}', 'store')->name('store')->middleware('can:crear_inscripcion');
});

// ---------------------------------------------------------------- Alumnos: Inscripción en Comisiones (CU3) y Cambio de Comisión (CU 19)
Route::controller(InscripcionComisionController::class)->prefix('alumnos/comisiones')->name('alumno-comisiones.')->group(function () {
    Route::get('confirmacion', 'confirmacion')->name('confirmacion')->middleware('can:crear_inscripcion_comision');
    Route::get('cambio/confirmacion', 'confirmacionCambio')->name('cambio.confirmacion')->middleware('can:editar_inscripcion_comision');
    Route::get('{alumno}', 'index')->name('index')->middleware('can:crear_inscripcion_comision');
    Route::get('{alumno}/inscribir', 'create')->name('create')->middleware('can:crear_inscripcion_comision');
    Route::post('{alumno}', 'store')->name('store')->middleware('can:crear_inscripcion_comision');
    Route::get('{alumno}/cambio', 'cambio')->name('cambio')->middleware('can:editar_inscripcion_comision');
    Route::put('{alumno}/cambio', 'cambiar')->name('cambio.update')->middleware('can:editar_inscripcion_comision');
});

// ---------------------------------------------------------------- Alumnos: Regularidad Académica por Materia (CU4; Preceptor y Docente, §7-14)
Route::controller(RegularidadController::class)->prefix('alumnos/regularidad')->name('regularidad.')->middleware('can:crear_regularidad')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('confirmacion', 'confirmacion')->name('confirmacion');
    Route::get('comisiones/{comision}', 'materias')->name('materias');
    Route::get('materias/{detComision}', 'alumnos')->name('alumnos');
    Route::get('inscripciones/{inscripcion}/registrar', 'edit')->name('edit');
    Route::put('inscripciones/{inscripcion}', 'update')->name('update');
});

// ---------------------------------------------------------------- Alumnos: Inscripción a Exámenes (CU5; Preceptor y Alumno, AlumnoPolicy)
Route::controller(InscripcionExamenController::class)->prefix('alumnos/examenes')->name('inscripciones-examen.')->group(function () {
    Route::get('confirmacion', 'confirmacion')->name('confirmacion')->middleware('can:crear_inscripcion_examen');
    Route::get('comprobantes/{detalleActa}', 'comprobante')->name('comprobante')->middleware('can:exportar_inscripcion_examen');
    Route::get('{alumno}', 'create')->name('create')->middleware('can:crear_inscripcion_examen');
    Route::post('{alumno}', 'store')->name('store')->middleware('can:crear_inscripcion_examen');
});

// ---------------------------------------------------------------- Exámenes: Asentamiento de Acta (CU6; desde el 👁 de Gestión de Actas)
Route::controller(AsentamientoActaController::class)->prefix('examenes/asentamiento')->name('asentamiento.')->middleware('can:crear_asentamiento_acta')->group(function () {
    Route::get('confirmacion', 'confirmacion')->name('confirmacion');
    Route::get('inscriptos/{detalleActa}/nota', 'edit')->name('edit');
    Route::put('inscriptos/{detalleActa}', 'update')->name('update');
    Route::get('{acta}', 'show')->name('show');
    Route::post('{acta}', 'store')->name('store');
});

// ---------------------------------------------------------------- Finanzas: Cajas (CU 35, 36)
Route::controller(CajaController::class)->prefix('finanzas/cajas')->name('cajas.')->group(function () {
    Route::get('/', 'index')->name('index')->middleware('can:listar_caja');
    Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_caja');
    Route::get('confirmacion', 'confirmacion')->name('confirmacion')->middleware('can:listar_caja');
    Route::get('abrir', 'create')->name('create')->middleware('can:crear_caja');
    Route::post('/', 'store')->name('store')->middleware('can:crear_caja');
    Route::get('{caja}', 'show')->name('show')->middleware('can:listar_caja');
    Route::post('{caja}/cierre', 'cerrar')->name('cerrar')->middleware('can:editar_caja');
});

// ---------------------------------------------------------------- Finanzas: Registrar Pago de Cuotas (CU7)
Route::controller(PagoCuotaController::class)->prefix('finanzas/pagos')->name('pagos.')->group(function () {
    Route::get('confirmacion', 'confirmacion')->name('confirmacion')->middleware('can:crear_pago');
    Route::get('alumnos/{alumno}', 'create')->name('create')->middleware('can:crear_pago');
    Route::post('alumnos/{alumno}', 'store')->name('store')->middleware('can:crear_pago');
    Route::get('comprobantes/{factura}', 'comprobante')->name('comprobante')->middleware('can:exportar_pago');
});

// ---------------------------------------------------------------- Personal (CU 25, 26, 27)
Route::controller(PersonalController::class)->prefix('personal')->name('personal.')->group(function () {
    Route::get('/', 'index')->name('index')->middleware('can:listar_personal');
    Route::post('datatable', 'datatable')->name('datatable')->middleware('can:listar_personal');
    Route::get('confirmacion', 'confirmacion')->name('confirmacion')->middleware('can:listar_personal');
    Route::get('localidades/{provincia}', LocalidadesPorProvinciaController::class)->name('localidades')->middleware('can:listar_personal');
    Route::get('registrar', 'create')->name('create')->middleware('can:crear_personal');
    Route::post('/', 'store')->name('store')->middleware('can:crear_personal');
    Route::post('verificar-dni', 'verificarDni')->name('verificar-dni')->middleware('can:crear_personal');
    Route::get('{personal}/modificar', 'edit')->name('edit')->middleware('can:editar_personal');
    Route::put('{personal}', 'update')->name('update')->middleware('can:editar_personal');
    Route::post('{personal}/baja', 'registrarBaja')->name('baja')->middleware('can:eliminar_personal');
});

// Ruta temporal para la revisión visual local del modal de confirmación; se elimina al finalizar.
Route::get('__review/confirmacion', fn () => view('panel._confirmacion', [
    'encabezado' => 'GESTIÓN ACADÉMICA',
    'titulo' => 'Carrera registrada exitosamente',
    'mensaje' => 'La operación se completó correctamente.',
    'tituloLineas' => 'Detalle del registro',
    'lineas' => ['Carrera: Tecnicatura en Redes', 'Modalidad: Presencial · Duración: 3 años', 'Estado: Activo'],
    'urlAceptar' => route('panel.inicio'),
]));
