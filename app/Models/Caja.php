<?php

namespace App\Models;

use App\Enums\EstadoCaja;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clase Caja (Hoja 4): la abre y cierra el Cajero (CU 35/36). */
class Caja extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'cajas';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'fecha_apertura', 'monto_inicial', 'fecha_cierre', 'monto_final',
        'diferencia_total', 'observacion_cierre', 'estado_caja',
    ];

    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'datetime',
            'monto_inicial' => 'decimal:2',
            'fecha_cierre' => 'datetime',
            'monto_final' => 'decimal:2',
            'diferencia_total' => 'decimal:2',
            'estado_caja' => EstadoCaja::class,
        ];
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function detallesCierre(): HasMany
    {
        return $this->hasMany(DetalleCierreCaja::class);
    }
}
