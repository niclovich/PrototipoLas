<?php

namespace App\Models;

use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Clase SolicitudBeca (Hoja 4). estado_solicitud queda como texto sin Enum: la tesis no define
 * sus valores y Becas está fuera de alcance (plan §7-8).
 */
class SolicitudBeca extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'solicitudes_beca';

    protected $primaryKey = 'id';

    protected $fillable = [
        'alumno_id', 'convocatoria_beca_id', 'situacion_economica', 'motivo_solicitud',
        'fecha_solicitud', 'estado_solicitud', 'motivo_rechazo',
    ];

    protected function casts(): array
    {
        return ['fecha_solicitud' => 'date'];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(ConvocatoriaBeca::class, 'convocatoria_beca_id');
    }

    public function beca(): HasOne
    {
        return $this->hasOne(Beca::class);
    }
}
