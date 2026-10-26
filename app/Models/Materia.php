<?php

namespace App\Models;

use App\Enums\EstadoGeneral;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clase Materia (Hoja 2). */
class Materia extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'materias';

    protected $primaryKey = 'id';

    protected $fillable = ['nombre_materia', 'estado_materia'];

    protected function casts(): array
    {
        return ['estado_materia' => EstadoGeneral::class];
    }

    public function detallesPlan(): HasMany
    {
        return $this->hasMany(DetallePlanEstudio::class);
    }
}
