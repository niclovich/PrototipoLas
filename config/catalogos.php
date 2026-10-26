<?php

use App\Models\Cuatrimestre;
use App\Models\Duracion;
use App\Models\Llamado;
use App\Models\Localidad;
use App\Models\MedioPago;
use App\Models\Modalidad;
use App\Models\Motivo;
use App\Models\Provincia;
use App\Models\Regimen;
use App\Models\RolTribunal;
use App\Models\TipoActa;
use App\Models\TipoDocumento;
use App\Models\TipoGestionTitulo;

/*
| Catálogos sin CU propio (plan §3.3) para el ABM genérico del SUPERADMIN.
| campos: columna => [label, tipo (texto|numero|select), max, min, unico (bool), unico_con (columnas),
|          modelo + texto + relacion (para select)]. El primer campo de tipo texto es el principal.
*/

return [

    'provincias' => [
        'titulo' => 'Provincias', 'singular' => 'Provincia', 'modelo' => Provincia::class,
        'campos' => ['provincia' => ['label' => 'Provincia', 'max' => 100, 'unico' => true]],
    ],

    'localidades' => [
        'titulo' => 'Localidades', 'singular' => 'Localidad', 'modelo' => Localidad::class,
        'campos' => [
            'provincia_id' => ['label' => 'Provincia', 'tipo' => 'select', 'modelo' => Provincia::class, 'texto' => 'provincia', 'relacion' => 'provincia'],
            'localidad' => ['label' => 'Localidad', 'max' => 100, 'unico' => true, 'unico_con' => ['provincia_id']],
        ],
    ],

    'modalidades' => [
        'titulo' => 'Modalidades', 'singular' => 'Modalidad', 'modelo' => Modalidad::class,
        'campos' => ['modalidad' => ['label' => 'Modalidad', 'max' => 50, 'unico' => true]],
    ],

    'duraciones' => [
        'titulo' => 'Duraciones', 'singular' => 'Duración', 'modelo' => Duracion::class,
        'campos' => [
            'duracion' => ['label' => 'Duración', 'max' => 50, 'unico' => true],
            'cantidad_anios' => ['label' => 'Cantidad de años', 'tipo' => 'numero', 'min' => 1, 'max' => 10],
        ],
    ],

    'regimenes' => [
        'titulo' => 'Regímenes', 'singular' => 'Régimen', 'modelo' => Regimen::class,
        'campos' => ['regimen' => ['label' => 'Régimen', 'max' => 50, 'unico' => true]],
    ],

    'cuatrimestres' => [
        'titulo' => 'Cuatrimestres', 'singular' => 'Cuatrimestre', 'modelo' => Cuatrimestre::class,
        'campos' => ['cuatrimestre' => ['label' => 'Cuatrimestre', 'max' => 50, 'unico' => true]],
    ],

    'llamados' => [
        'titulo' => 'Llamados', 'singular' => 'Llamado', 'modelo' => Llamado::class,
        'campos' => ['llamado' => ['label' => 'Llamado', 'max' => 50, 'unico' => true]],
    ],

    'tipos-acta' => [
        'titulo' => 'Tipos de Acta', 'singular' => 'Tipo de Acta', 'modelo' => TipoActa::class,
        'campos' => ['descripcion' => ['label' => 'Descripción', 'max' => 50, 'unico' => true]],
    ],

    'roles-tribunal' => [
        'titulo' => 'Roles del Tribunal', 'singular' => 'Rol del Tribunal', 'modelo' => RolTribunal::class,
        'campos' => ['descripcion' => ['label' => 'Descripción', 'max' => 50, 'unico' => true]],
    ],

    'tipos-documento' => [
        'titulo' => 'Tipos de Documento', 'singular' => 'Tipo de Documento', 'modelo' => TipoDocumento::class,
        'campos' => ['descripcion' => ['label' => 'Descripción', 'max' => 80, 'unico' => true]],
    ],

    'motivos' => [
        'titulo' => 'Motivos', 'singular' => 'Motivo', 'modelo' => Motivo::class,
        'campos' => [
            'descripcion' => ['label' => 'Descripción', 'max' => 150, 'unico' => true, 'unico_con' => ['accion']],
            'accion' => ['label' => 'Acción', 'max' => 50],
        ],
    ],

    'medios-pago' => [
        'titulo' => 'Medios de Pago', 'singular' => 'Medio de Pago', 'modelo' => MedioPago::class,
        'campos' => ['medio_pago' => ['label' => 'Medio de pago', 'max' => 50, 'unico' => true]],
    ],

    'tipos-gestion-titulo' => [
        'titulo' => 'Tipos de Gestión de Título', 'singular' => 'Tipo de Gestión de Título', 'modelo' => TipoGestionTitulo::class,
        'campos' => ['descripcion' => ['label' => 'Descripción', 'max' => 80, 'unico' => true]],
    ],

];
