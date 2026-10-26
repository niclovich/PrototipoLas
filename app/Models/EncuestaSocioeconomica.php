<?php

namespace App\Models;

use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase EncuestaSocioeconomica (Hoja 1): Usuario 1..1 — 0..1 (CU1 P3). */
class EncuestaSocioeconomica extends Model
{
    use AuditaCambios, HasFactory;

    public const PREGUNTAS = [
        'tiene_hijos', 'vive_solo', 'padres_viven', 'madre_trabaja', 'padre_trabaja', 'trabaja',
        'recibe_ayuda_economica', 'estudios_superiores_previos', 'posee_discapacidad',
        'miembro_poblacion_indigena', 'hablante_lenguas_indigenas', 'sabe_usar_pc', 'es_usuario_internet',
    ];

    protected $table = 'encuestas_socioeconomicas';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', ...self::PREGUNTAS, 'nivel_estudios_madre', 'nivel_estudios_padre', 'idiomas',
    ];

    protected function casts(): array
    {
        return array_fill_keys(self::PREGUNTAS, 'boolean');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
