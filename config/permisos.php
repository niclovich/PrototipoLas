<?php

use App\Enums\RolSistema;

/*
| Matriz de permisos del plan general (docs/planes/00_general.md §2.2).
| Formato {accion}_{entidad}. SUPERADMIN recibe todos los permisos y, además,
| conserva el respaldo global de Gate::before.
| La usan RolesPermisosSeeder, el formulario de Roles y los tests.
*/

$permisos = [
    // Académico
    'listar_carrera', 'crear_carrera', 'editar_carrera',
    'listar_materia', 'crear_materia', 'editar_materia',
    'listar_plan_estudio', 'crear_plan_estudio', 'crear_materia_plan', 'eliminar_materia_plan',
    'crear_correlativa',
    'listar_comision', 'crear_comision',
    // Alumnos
    'listar_alumno', 'crear_preinscripcion', 'crear_usuario_alumno', 'crear_inscripcion',
    'crear_inscripcion_comision', 'editar_inscripcion_comision', 'crear_regularidad',
    // Exámenes
    'listar_acta_examen', 'crear_acta_examen', 'editar_acta_examen',
    'crear_inscripcion_examen', 'exportar_inscripcion_examen', 'crear_asentamiento_acta',
    // Finanzas
    'listar_cuota', 'crear_cuota', 'editar_cuota',
    'listar_caja', 'crear_caja', 'editar_caja',
    'crear_pago', 'exportar_pago',
    // Personal
    'listar_personal', 'crear_personal', 'editar_personal', 'eliminar_personal',
    // Administración (base común)
    'listar_usuario', 'crear_usuario', 'editar_usuario', 'eliminar_usuario',
    'listar_rol', 'crear_rol', 'editar_rol', 'eliminar_rol',
    'listar_catalogo', 'crear_catalogo', 'editar_catalogo', 'eliminar_catalogo',
    'listar_auditoria', 'ver_auditoria',
];

return [

    'permisos' => $permisos,

    // Etiquetas para la pantalla de Roles (el nombre técnico se muestra como tooltip).
    'acciones' => [
        'listar' => 'Listar',
        'ver' => 'Ver detalle',
        'crear' => 'Registrar',
        'editar' => 'Modificar',
        'eliminar' => 'Dar de baja',
        'exportar' => 'Comprobante / exportar',
    ],

    'entidades' => [
        'carrera' => 'Carreras',
        'materia' => 'Materias',
        'plan_estudio' => 'Planes de Estudio',
        'materia_plan' => 'Materias del Plan',
        'correlativa' => 'Correlatividades',
        'comision' => 'Comisiones',
        'alumno' => 'Gestión de Alumnos',
        'preinscripcion' => 'Preinscripción',
        'usuario_alumno' => 'Usuarios Alumno',
        'inscripcion' => 'Inscripción',
        'inscripcion_comision' => 'Inscripción en Comisiones',
        'regularidad' => 'Regularidad Académica',
        'acta_examen' => 'Actas de Examen',
        'inscripcion_examen' => 'Inscripción a Exámenes',
        'asentamiento_acta' => 'Asentamiento de Acta',
        'cuota' => 'Cuotas por Carrera',
        'caja' => 'Cajas',
        'pago' => 'Pagos de Cuotas',
        'personal' => 'Personal',
        'usuario' => 'Usuarios',
        'rol' => 'Roles y Permisos',
        'catalogo' => 'Catálogos',
        'auditoria' => 'Auditoría',
    ],

    'roles' => [
        RolSistema::DirectorGeneral->value => [
            'listar_cuota', 'crear_cuota', 'editar_cuota',
            'listar_personal', 'crear_personal', 'editar_personal', 'eliminar_personal',
        ],
        RolSistema::Coordinador->value => [
            'listar_carrera', 'crear_carrera', 'editar_carrera',
            'listar_materia', 'crear_materia', 'editar_materia',
            'listar_plan_estudio', 'crear_plan_estudio', 'crear_materia_plan', 'eliminar_materia_plan',
            'crear_correlativa',
            'listar_comision', 'crear_comision',
            'listar_acta_examen', 'crear_acta_examen', 'editar_acta_examen',
            'listar_cuota', 'crear_cuota', 'editar_cuota',
        ],
        RolSistema::Preceptor->value => [
            'listar_comision',
            'listar_alumno', 'crear_preinscripcion', 'crear_usuario_alumno', 'crear_inscripcion',
            'crear_inscripcion_comision', 'editar_inscripcion_comision', 'crear_regularidad',
            'listar_acta_examen', 'crear_inscripcion_examen', 'exportar_inscripcion_examen',
            'crear_asentamiento_acta',
        ],
        RolSistema::AuxiliarAdministrativo->value => [
            'listar_alumno', 'crear_preinscripcion', 'crear_usuario_alumno', 'crear_inscripcion',
        ],
        RolSistema::Docente->value => [
            'listar_comision', 'crear_regularidad',
        ],
        RolSistema::Cajero->value => [
            'listar_alumno',
            'listar_caja', 'crear_caja', 'editar_caja',
            'crear_pago', 'exportar_pago',
        ],
        RolSistema::Alumno->value => [
            'listar_alumno', 'crear_inscripcion_examen', 'exportar_inscripcion_examen',
        ],
        RolSistema::SuperAdmin->value => $permisos,
    ],

];
