<?php

namespace App\Models;

use App\Enums\Idioma;
use App\Enums\NivelEstudios;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase EncuestaSocioeconomica (Hoja 1): Usuario 1..1 — 0..1 (CU1 P3, CU 9). */
class EncuestaSocioeconomica extends Model
{
    use AuditaCambios, HasFactory;

    public const PREGUNTAS = [
        'tiene_hijos', 'vive_solo', 'padres_viven', 'madre_trabaja', 'padre_trabaja', 'trabaja',
        'recibe_ayuda_economica', 'estudios_superiores_previos', 'posee_discapacidad',
        'miembro_poblacion_indigena', 'hablante_lenguas_indigenas', 'sabe_usar_pc', 'es_usuario_internet',
    ];

    /** Textos de CU1 P3 (Paso_3_Encuesta.png), por sección y en el orden del wireframe. */
    public const SECCIONES = [
        'GRUPO FAMILIAR' => [
            'tiene_hijos' => 'Tiene hijos a cargo',
            'vive_solo' => 'Vive solo/a',
            'padres_viven' => 'Ambos padres viven',
            'madre_trabaja' => 'La madre trabaja',
            'padre_trabaja' => 'El padre trabaja',
        ],
        'SITUACION ECONOMICA' => [
            'trabaja' => 'Trabaja actualmente',
            'recibe_ayuda_economica' => 'Recibe ayuda economica',
            'estudios_superiores_previos' => 'Estudios superiores previos',
        ],
        'IDIOMAS Y TECNOLOGIA' => [
            'sabe_usar_pc' => 'Sabe usar computadora',
            'es_usuario_internet' => 'Usuario de internet',
        ],
        'DIVERSIDAD Y ACCESIBILIDAD' => [
            'miembro_poblacion_indigena' => 'Poblacion indigena',
            'hablante_lenguas_indigenas' => 'Lenguas indigenas',
            'posee_discapacidad' => 'Posee discapacidad',
        ],
    ];

    /** Separador de "Idiomas que habla" en la columna `idiomas` (string 255). */
    public const SEPARADOR_IDIOMAS = ', ';

    protected $table = 'encuestas_socioeconomicas';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', ...self::PREGUNTAS, 'nivel_estudios_madre', 'nivel_estudios_padre', 'idiomas',
    ];

    protected function casts(): array
    {
        return [
            ...array_fill_keys(self::PREGUNTAS, 'boolean'),
            'nivel_estudios_madre' => NivelEstudios::class,
            'nivel_estudios_padre' => NivelEstudios::class,
        ];
    }

    /**
     * Idiomas como lista de valores de Idioma; en la BD, "Español, Guaraní".
     */
    protected function idiomas(): Attribute
    {
        return Attribute::make(
            get: fn (?string $valor) => $valor === null || $valor === '' ? [] : explode(self::SEPARADOR_IDIOMAS, $valor),
            set: function (mixed $valor) {
                $lista = collect(is_array($valor) ? $valor : ($valor === null ? [] : [$valor]))
                    ->map(fn ($idioma) => $idioma instanceof Idioma ? $idioma->value : (string) $idioma)
                    ->filter()
                    ->unique()
                    ->values();

                return $lista->isEmpty() ? null : $lista->implode(self::SEPARADOR_IDIOMAS);
            },
        );
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
