<?php

namespace App\Models;

use App\Enums\Asentada;
use App\Enums\EstadoActa;
use App\Enums\EstadoDetalleActa;
use App\Models\Concerns\AuditaCambios;
use App\Models\Concerns\BuscaPorPalabras;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clase ActaExamen (Hoja 3): se crea "Pendiente" con asentada = N; al asentar → "Finalizado", S y nro_folio. */
class ActaExamen extends Model
{
    use AuditaCambios, BuscaPorPalabras, HasFactory;

    protected $table = 'actas_examen';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nro_acta', 'detalle_plan_estudio_id', 'modalidad_id', 'llamado_id', 'tipo_acta_id', 'tribunal_id',
        'fecha_acta', 'estado_acta', 'asentada', 'nro_folio',
    ];

    protected function casts(): array
    {
        return [
            'nro_acta' => 'integer',
            'fecha_acta' => 'date',
            'estado_acta' => EstadoActa::class,
            'asentada' => Asentada::class,
            'nro_folio' => 'integer',
        ];
    }

    public function detallePlan(): BelongsTo
    {
        return $this->belongsTo(DetallePlanEstudio::class, 'detalle_plan_estudio_id');
    }

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class)->withTrashed();
    }

    public function llamado(): BelongsTo
    {
        return $this->belongsTo(Llamado::class)->withTrashed();
    }

    public function tipoActa(): BelongsTo
    {
        return $this->belongsTo(TipoActa::class)->withTrashed();
    }

    public function tribunal(): BelongsTo
    {
        return $this->belongsTo(Tribunal::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleActa::class);
    }

    /** Alumnos inscriptos (DetalleActa "Activo", CU5). */
    public function inscriptos(): HasMany
    {
        return $this->detalles()->where('estado_detalle_acta', EstadoDetalleActa::Activo->value);
    }

    /** Nro de acta con 4 dígitos ("0142"), como en los wireframes. */
    protected function nroActaTexto(): Attribute
    {
        return Attribute::get(fn () => str_pad((string) $this->nro_acta, 4, '0', STR_PAD_LEFT));
    }
}
