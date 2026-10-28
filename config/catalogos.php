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
| genero: m|f (textos "Nuevo/Nueva"). bloquean_baja: relación hija => mensaje.
| campos: columna => [label, tipo (texto|numero|select|lista + valores), max, min, unico (bool), unico_con (columnas),
|          modelo + texto + relacion (para select)]. El primer campo de tipo texto es el principal.
*/

return [

    'provincias' => [
        'titulo' => 'Provincias', 'singular' => 'Provincia', 'genero' => 'f', 'modelo' => Provincia::class,
        'bloquean_baja' => ['localidades' => 'No se puede dar de baja una provincia con localidades activas. Dé de baja primero sus localidades.'],
        'campos' => ['provincia' => ['label' => 'Provincia', 'max' => 100, 'unico' => true]],
    ],

    'localidades' => [
        'titulo' => 'Localidades', 'singular' => 'Localidad', 'genero' => 'f', 'modelo' => Localidad::class,
        'campos' => [
            'provincia_id' => ['label' => 'Provincia', 'tipo' => 'select', 'modelo' => Provincia::class, 'texto' => 'provincia', 'relacion' => 'provincia'],
            'localidad' => ['label' => 'Localidad', 'max' => 100, 'unico' => true, 'unico_con' => ['provincia_id']],
        ],
    ],

    'modalidades' => [
        'titulo' => 'Modalidades', 'singular' => 'Modalidad', 'genero' => 'f', 'modelo' => Modalidad::class,
        'campos' => ['modalidad' => ['label' => 'Modalidad', 'max' => 50, 'unico' => true]],
    ],

    'duraciones' => [
        'titulo' => 'Duraciones', 'singular' => 'Duración', 'genero' => 'f', 'modelo' => Duracion::class,
        'campos' => [
            'duracion' => ['label' => 'Duración', 'max' => 50, 'unico' => true],
            'cantidad_anios' => ['label' => 'Cantidad de años', 'tipo' => 'numero', 'min' => 1, 'max' => 10],
        ],
    ],

    'regimenes' => [
        'titulo' => 'Regímenes', 'singular' => 'Régimen', 'genero' => 'm', 'modelo' => Regimen::class,
        'campos' => ['regimen' => ['label' => 'Régimen', 'max' => 50, 'unico' => true]],
    ],

    'cuatrimestres' => [
        'titulo' => 'Cuatrimestres', 'singular' => 'Cuatrimestre', 'genero' => 'm', 'modelo' => Cuatrimestre::class,
        'campos' => ['cuatrimestre' => ['label' => 'Cuatrimestre', 'max' => 50, 'unico' => true]],
    ],

    'llamados' => [
        'titulo' => 'Llamados', 'singular' => 'Llamado', 'genero' => 'm', 'modelo' => Llamado::class,
        'campos' => ['llamado' => ['label' => 'Llamado', 'max' => 50, 'unico' => true]],
    ],

    'tipos-acta' => [
        'titulo' => 'Tipos de Acta', 'singular' => 'Tipo de Acta', 'genero' => 'm', 'modelo' => TipoActa::class,
        'campos' => ['descripcion' => ['label' => 'Descripción', 'max' => 50, 'unico' => true]],
    ],

    'roles-tribunal' => [
        'titulo' => 'Roles del Tribunal', 'singular' => 'Rol del Tribunal', 'genero' => 'm', 'modelo' => RolTribunal::class,
        'campos' => ['descripcion' => ['label' => 'Descripción', 'max' => 50, 'unico' => true]],
    ],

    'tipos-documento' => [
        'titulo' => 'Tipos de Documento', 'singular' => 'Tipo de Documento', 'genero' => 'm', 'modelo' => TipoDocumento::class,
        'campos' => ['descripcion' => ['label' => 'Descripción', 'max' => 80, 'unico' => true]],
    ],

    'motivos' => [
        'titulo' => 'Motivos', 'singular' => 'Motivo', 'genero' => 'm', 'modelo' => Motivo::class,
        'campos' => [
            'descripcion' => ['label' => 'Descripción', 'max' => 150, 'unico' => true, 'unico_con' => ['accion']],
            'accion' => ['label' => 'Acción', 'tipo' => 'lista', 'valores' => Motivo::ACCIONES],
        ],
    ],

    'medios-pago' => [
        'titulo' => 'Medios de Pago', 'singular' => 'Medio de Pago', 'genero' => 'm', 'modelo' => MedioPago::class,
        'campos' => ['medio_pago' => ['label' => 'Medio de pago', 'max' => 50, 'unico' => true]],
    ],

    'tipos-gestion-titulo' => [
        'titulo' => 'Tipos de Gestión de Título', 'singular' => 'Tipo de Gestión de Título', 'genero' => 'm', 'modelo' => TipoGestionTitulo::class,
        'campos' => ['descripcion' => ['label' => 'Descripción', 'max' => 80, 'unico' => true]],
    ],

];
