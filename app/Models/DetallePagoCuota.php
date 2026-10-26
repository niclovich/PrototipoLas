<?php

namespace App\Models;

use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase DetallePagoCuota (Hoja 4): cada uno cancela una Cuota; la Beca puede bonificar. */
class DetallePagoCuota extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'detalles_pago_cuota';

    protected $primaryKey = 'id';

    protected $fillable = ['pago_id', 'cuota_id', 'beca_id', 'monto_aplicado', 'monto_bonificado'];

    protected function casts(): array
    {
        return ['monto_aplicado' => 'decimal:2', 'monto_bonificado' => 'decimal:2'];
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class);
    }

    public function cuota(): BelongsTo
    {
        return $this->belongsTo(Cuota::class);
    }

    public function beca(): BelongsTo
    {
        return $this->belongsTo(Beca::class);
    }
}
