<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Clase Pago (Hoja 4): un solo MedioPago por pago (decisión §7-5); 1..* DetallePagoCuota; 1..1 FacturaComprobante. */
class Pago extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'pagos';

    protected $primaryKey = 'id';

    protected $fillable = ['alumno_id', 'caja_id', 'medio_pago_id', 'fecha_pago', 'monto_total', 'estado_pago'];

    protected function casts(): array
    {
        return ['fecha_pago' => 'datetime', 'monto_total' => 'decimal:2', 'estado_pago' => EstadoGeneral::class];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class)->withTrashed();
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePagoCuota::class);
    }

    public function factura(): HasOne
    {
        return $this->hasOne(FacturaComprobante::class);
    }
}
