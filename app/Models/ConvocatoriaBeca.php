<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clase ConvocatoriaBeca (Hoja 4). */
class ConvocatoriaBeca extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'convocatorias_beca';

    protected $primaryKey = 'id';

    protected $fillable = ['tipo_beca_id', 'anio_lectivo', 'fecha_desde', 'fecha_hasta', 'cantidad_disponible', 'estado_convocatoria'];

    protected function casts(): array
    {
        return [
            'anio_lectivo' => 'integer',
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
            'cantidad_disponible' => 'integer',
            'estado_convocatoria' => EstadoGeneral::class,
        ];
    }

    public function tipoBeca(): BelongsTo
    {
        return $this->belongsTo(TipoBeca::class);
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(SolicitudBeca::class);
    }

    public function becas(): HasMany
    {
        return $this->hasMany(Beca::class);
    }
}
