<?php

namespace App\Models;

use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase DetalleCierreCaja (Hoja 4): uno por medio de pago al cerrar la caja. */
class DetalleCierreCaja extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'detalles_cierre_caja';

    protected $primaryKey = 'id';

    protected $fillable = ['caja_id', 'medio_pago_id', 'monto_esperado', 'monto_contado', 'diferencia'];

    protected function casts(): array
    {
        return ['monto_esperado' => 'decimal:2', 'monto_contado' => 'decimal:2', 'diferencia' => 'decimal:2'];
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class)->withTrashed();
    }
}
