<?php

namespace App\Http\Requests\Examenes;

use App\Http\Requests\Examenes\Concerns\ReglasActaExamen;
use App\Models\Carrera;
use App\Services\Examenes\ActaExamenService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** CU 21 pasos 4–25 (P2 Registrar Acta de Examen). */
class RegistrarActaExamenRequest extends FormRequest
{
    use ReglasActaExamen;

    public function rules(): array
    {
        /** @var Carrera $carrera */
        $carrera = $this->route('carrera');
        $ids = app(ActaExamenService::class)->detallesDe($carrera)->pluck('id')->all();

        return [
            'detalle_plan_estudio_id' => ['required', 'integer', Rule::in($ids)],
            ...$this->reglasActa(),
        ];
    }
}
