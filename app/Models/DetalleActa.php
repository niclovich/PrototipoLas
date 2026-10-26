<?php

namespace App\Models;

use App\Enums\CondicionExamen;
use App\Enums\EstadoDetalleActa;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase DetalleActa (Hoja 3): inscripción del alumno al acta (CU5) y su nota (CU6). uuid = QR del comprobante. */
class DetalleActa extends Model
{
    use AuditaCambios, HasFactory, HasUuids;

    protected $table = 'detalles_acta';

    protected $primaryKey = 'id';

    protected $fillable = ['acta_examen_id', 'alumno_id', 'nota_acta', 'observacion', 'estado_detalle_acta', 'condicion_final'];

    /** @return list<string> uuid es un identificador público, no la PK. */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected function casts(): array
    {
        return [
            'nota_acta' => 'decimal:2',
            'estado_detalle_acta' => EstadoDetalleActa::class,
            'condicion_final' => CondicionExamen::class,
        ];
    }

    public function acta(): BelongsTo
    {
        return $this->belongsTo(ActaExamen::class, 'acta_examen_id');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }
}
