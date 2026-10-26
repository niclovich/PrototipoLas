<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase FacturaComprobante (Hoja 4): Pago 1..1; uuid para el QR de validación pública. */
class FacturaComprobante extends Model
{
    use AuditaCambios, HasFactory, HasUuids;

    protected $table = 'facturas_comprobante';

    protected $primaryKey = 'id';

    protected $fillable = ['pago_id', 'numero_comprobante', 'fecha_emision', 'estado_factura'];

    /** @return list<string> */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected function casts(): array
    {
        return ['numero_comprobante' => 'integer', 'fecha_emision' => 'date', 'estado_factura' => EstadoGeneral::class];
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class);
    }

    /** Número visible: punto de venta + número, p. ej. 0001-00000025 (plan §7-6). */
    public function numeroFormateado(): string
    {
        return config('las.punto_venta').'-'.str_pad((string) $this->numero_comprobante, 8, '0', STR_PAD_LEFT);
    }
}
