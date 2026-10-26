<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clase TipoBeca (Hoja 4). Becas fuera de alcance: sin pantallas, datos por seeder (plan §7-8). */
class TipoBeca extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'tipos_beca';

    protected $primaryKey = 'id';

    protected $fillable = ['nombre_beca', 'tipo_descuento', 'valor_descuento', 'requisito_academico', 'requisito_economico', 'estado_beca'];

    protected function casts(): array
    {
        return ['valor_descuento' => 'decimal:2', 'estado_beca' => EstadoGeneral::class];
    }

    public function convocatorias(): HasMany
    {
        return $this->hasMany(ConvocatoriaBeca::class);
    }
}
