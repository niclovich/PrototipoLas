<?php

namespace App\Http\Requests\Alumnos;

use App\Enums\Idioma;
use App\Enums\NivelEstudios;
use App\Models\EncuestaSocioeconomica;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * CU 9 paso 5 (Encuesta socioeconómica), pantalla P3 de CU1. Nada es obligatorio (D2):
 * checkbox desmarcado = false; selects e idiomas vacíos = null.
 */
class EncuestaSocioeconomicaRequest extends FormRequest
{
    public const MENSAJE_NIVEL = 'Seleccione un nivel de estudios de la lista.';

    public const MENSAJE_IDIOMAS = 'Seleccione idiomas de la lista.';

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('crear_usuario_alumno');
    }

    /**
     * Un checkbox desmarcado no viaja en el formulario: solo los AUSENTES pasan a false ("No"); los presentes
     * se validan con `boolean` (la vista usa value="1"). Sin idiomas elegidos = lista vacía.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            ...collect(EncuestaSocioeconomica::PREGUNTAS)
                ->reject(fn (string $p) => $this->has($p))
                ->mapWithKeys(fn (string $p) => [$p => false])
                ->all(),
            ...($this->has('idiomas') ? [] : ['idiomas' => []]),
        ]);
    }

    /** @return array<string, mixed> */
    public static function reglas(): array
    {
        return [
            ...array_fill_keys(EncuestaSocioeconomica::PREGUNTAS, ['required', 'boolean']),
            'nivel_estudios_madre' => ['nullable', Rule::enum(NivelEstudios::class)],
            'nivel_estudios_padre' => ['nullable', Rule::enum(NivelEstudios::class)],
            'idiomas' => ['array'],
            'idiomas.*' => ['distinct', Rule::enum(Idioma::class)],
        ];
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return static::reglas();
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nivel_estudios_madre.enum' => self::MENSAJE_NIVEL,
            'nivel_estudios_padre.enum' => self::MENSAJE_NIVEL,
            'idiomas.array' => self::MENSAJE_IDIOMAS,
            'idiomas.*.enum' => self::MENSAJE_IDIOMAS,
            'idiomas.*.distinct' => self::MENSAJE_IDIOMAS,
        ];
    }

    /** @return array<string, mixed> datos ya validados de la Encuesta */
    public function datosEncuesta(): array
    {
        return static::paraEncuesta($this->validated());
    }

    /**
     * Normaliza datos ya validados (del request o re-validados desde la sesión del wizard en P5):
     * las 13 preguntas como bool.
     *
     * @param  array<string, mixed>  $validados
     * @return array<string, mixed>
     */
    public static function paraEncuesta(array $validados): array
    {
        foreach (EncuestaSocioeconomica::PREGUNTAS as $pregunta) {
            $validados[$pregunta] = filter_var($validados[$pregunta] ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        return $validados;
    }
}
