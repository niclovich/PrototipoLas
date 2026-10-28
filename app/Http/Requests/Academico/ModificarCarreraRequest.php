<?php

namespace App\Http\Requests\Academico;

use App\Enums\EstadoCarrera;
use App\Http\Requests\Academico\Concerns\ReglasCarrera;
use App\Models\Carrera;
use App\Rules\ResolucionMinisterial;
use App\Services\Academico\CarreraService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * CU 4: P2 Modificar Carrera. Con Plan de Estudio activo solo se valida el Estado (paso 5). El formato de la
 * Resolución se valida solo si cambió (spec paso 9: "si se modificaron nombre o resolución").
 */
class ModificarCarreraRequest extends FormRequest
{
    use ReglasCarrera;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Carrera $carrera */
        $carrera = $this->route('carrera');
        $estado = ['estado_carrera' => ['required', Rule::enum(EstadoCarrera::class)]];

        if (app(CarreraService::class)->poseePlanActivo($carrera)) {
            return $estado;
        }

        $resolucionCambio = ResolucionMinisterial::normalizar((string) $this->input('resolucion_carrera')) !== $carrera->resolucion_carrera;

        return [
            ...$this->reglasDatosCarrera(
                $carrera->getKey(),
                $resolucionCambio,
                $carrera->modalidad_id,
                $carrera->duracion_id,
            ),
            ...$estado,
        ];
    }
}
