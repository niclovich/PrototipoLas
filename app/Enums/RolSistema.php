<?php

namespace App\Enums;

use App\Enums\Concerns\EnumConEtiqueta;

/**
 * Actores de la tesis (Hoja 1 del diagrama de 4 hojas) + SUPERADMIN técnico.
 * El valor es el nombre del rol en la tabla roles de Spatie.
 */
enum RolSistema: string
{
    use EnumConEtiqueta;

    case SuperAdmin = 'SUPERADMIN';
    case DirectorGeneral = 'Director General';
    case Coordinador = 'Coordinador';
    case Preceptor = 'Preceptor';
    case AuxiliarAdministrativo = 'Auxiliar Administrativo';
    case Docente = 'Docente';
    case Cajero = 'Cajero';
    case Alumno = 'Alumno';

    public function descripcion(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Administrador técnico del sistema (acceso total).',
            self::DirectorGeneral => 'Director General del instituto: personal y cuotas.',
            self::Coordinador => 'Coordinador de carrera: carreras, materias, planes, comisiones, actas y cuotas.',
            self::Preceptor => 'Preceptor: preinscripción, inscripciones, regularidad, exámenes y asentamiento de actas.',
            self::AuxiliarAdministrativo => 'Auxiliar Administrativo: preinscripción e inscripción de alumnos.',
            self::Docente => 'Docente: regularidad académica por materia.',
            self::Cajero => 'Cajero: apertura y cierre de caja, registro de pagos de cuotas.',
            self::Alumno => 'Alumno: inscripción a exámenes y sus comprobantes.',
        };
    }

    public function badge(): string
    {
        return $this === self::SuperAdmin ? 'danger' : 'info';
    }

    /** Actores del personal (CU 25: Director General, Coordinador, Preceptor, Auxiliar Administrativo, Docente, Cajero). */
    public static function delPersonal(): array
    {
        return [self::DirectorGeneral, self::Coordinador, self::Preceptor, self::AuxiliarAdministrativo, self::Docente, self::Cajero];
    }

    /** @return list<string> */
    public static function valoresDelPersonal(): array
    {
        return array_map(fn (self $rol) => $rol->value, self::delPersonal());
    }

    /** Roles que se pueden asignar desde el módulo Usuarios (el Alumno se crea en CU1/CU 9). */
    public static function asignablesDesdeUsuarios(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $rol) => $rol !== self::Alumno,
        ));
    }
}
