<?php

namespace App\Http\Requests\Academico;

use App\Http\Requests\Academico\Concerns\ReglasCarrera;
use Illuminate\Foundation\Http\FormRequest;

/** CU 3: P2 Registrar Carrera. El estado no viaja: lo fija el servidor ("Pendiente"). */
class RegistrarCarreraRequest extends FormRequest
{
    use ReglasCarrera;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->reglasDatosCarrera(null);
    }
}
