<?php

namespace App\Services\Academico;

use App\Enums\EstadoGeneral;
use App\Exceptions\OperacionNoPermitida;
use App\Models\Materia;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * CU 1 Registrar y CU 2 Modificar datos de una Materia (Coordinador de Carrera).
 */
class MateriaService
{
    /** CU 1 alt. 4.1 y CU 2 alt. 7.1. */
    public const MENSAJE_MATERIA_EXISTENTE = 'Ya existe una materia con ese nombre';

    /** CU 2 alt. 4.1. */
    public const MENSAJE_MATERIA_VINCULADA = 'No se puede modificar la materia porque ya se encuentra vinculada a un Plan de Estudio';

    /** Títulos de la Confirmación (modelo Registrar_P3 de Carrera; D6 de E5). */
    public const CONFIRMACION_REGISTRO = 'Materia registrada exitosamente';

    public const CONFIRMACION_MODIFICACION = 'Materia modificada exitosamente';

    /** CU 1 paso 7: siempre en estado "Activo" (decisión D1 de E5: el combo se muestra fijo y deshabilitado). */
    public function registrar(string $nombre): Materia
    {
        return $this->traducirNombreDuplicado(
            fn () => DB::transaction(fn () => Materia::create([
                'nombre_materia' => $nombre,
                'estado_materia' => EstadoGeneral::Activo,
            ])),
        );
    }

    /**
     * CU 2 pasos 4 y 10 (decisión D2 de E5: la vinculación se controla al tocar "Guardar Cambios").
     *
     * @throws OperacionNoPermitida si la materia está vinculada a un Plan de Estudio activo (alt. 4: fin del CU)
     */
    public function modificar(Materia $materia, string $nombre, EstadoGeneral $estado): Materia
    {
        return $this->traducirNombreDuplicado(
            fn () => DB::transaction(function () use ($materia, $nombre, $estado) {
                $materia = Materia::query()->lockForUpdate()->findOrFail($materia->getKey());

                if ($materia->vinculadaAPlanActivo()) {
                    throw new OperacionNoPermitida(self::MENSAJE_MATERIA_VINCULADA);
                }

                $materia->update(['nombre_materia' => $nombre, 'estado_materia' => $estado]);

                return $materia;
            }),
        );
    }

    /**
     * El FormRequest evita duplicados en el caso normal. Este control traduce la
     * colisión de BD si otra solicitud crea el mismo nombre entre validar y guardar.
     *
     * @param  Closure(): Materia  $operacion
     */
    private function traducirNombreDuplicado(Closure $operacion): Materia
    {
        try {
            return $operacion();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'nombre_materia' => self::MENSAJE_MATERIA_EXISTENTE,
            ]);
        }
    }

    /** @return list<string> líneas de la Confirmación */
    public function lineasConfirmacion(Materia $materia): array
    {
        return ["Materia: {$materia->nombre_materia}", "Estado: {$materia->estado_materia->label()}"];
    }
}
