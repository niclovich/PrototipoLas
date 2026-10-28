<?php

namespace App\Models;

use App\Models\Concerns\EsCatalogo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Catálogo MedioPago (plan §3.3). */
class MedioPago extends Model
{
    use EsCatalogo, HasFactory;

    /** Al Monto Esperado del Efectivo se le suma el Monto Inicial (CU 36, aclaración p. 162). */
    public const EFECTIVO = 'Efectivo';

    protected $table = 'medios_pago';

    protected $primaryKey = 'id';

    protected $fillable = ['medio_pago'];

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function detallesCierre(): HasMany
    {
        return $this->hasMany(DetalleCierreCaja::class);
    }
}
