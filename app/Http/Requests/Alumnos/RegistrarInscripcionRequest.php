<?php

namespace App\Http\Requests\Alumnos;

use App\Models\Alumno;
use App\Services\Alumnos\InscripcionService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * CU2 pasos 5–12: Certificado de Título y Fotocopia de DNI en PDF (alt. 7.1 / 11.1 "Formato Incompatible").
 * Obligatorios salvo que el usuario ya los haya presentado en CU1 (E14-D3).
 */
class RegistrarInscripcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('crear_inscripcion');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Alumno $alumno */
        $alumno = $this->route('alumno');
        $presentados = app(InscripcionService::class)->documentosPresentados($alumno);

        return collect(InscripcionService::DOCUMENTOS)
            ->mapWithKeys(fn (string $tipo, string $campo) => [$campo => [
                isset($presentados[$campo]) ? 'nullable' : 'required',
                'file', 'mimes:pdf', 'max:'.config('las.documento_max_kb'),
            ]])
            ->all();
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $mensajes = [];
        foreach (InscripcionService::DOCUMENTOS as $campo => $tipo) {
            $mensajes["{$campo}.required"] = "Adjunte el {$tipo} en formato PDF.";
            $mensajes["{$campo}.file"] = InscripcionService::MENSAJE_FORMATO;
            $mensajes["{$campo}.mimes"] = InscripcionService::MENSAJE_FORMATO;
        }

        return $mensajes;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return array_map('mb_strtolower', InscripcionService::DOCUMENTOS);
    }

    /** @return array<string, UploadedFile> */
    public function archivos(): array
    {
        return array_filter(array_map(fn (string $campo) => $this->file($campo), array_combine(
            array_keys(InscripcionService::DOCUMENTOS),
            array_keys(InscripcionService::DOCUMENTOS),
        )));
    }
}
