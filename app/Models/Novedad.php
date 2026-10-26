<?php

namespace App\Models;

use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase Novedad (Hojas 1 y 2): asociada a Usuario (obligatoria) y a Alumno (opcional). */
class Novedad extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'novedades';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'alumno_id', 'motivo_id', 'fecha_novedad', 'observacion_novedad'];

    protected function casts(): array
    {
        return ['fecha_novedad' => 'date'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function motivo(): BelongsTo
    {
        return $this->belongsTo(Motivo::class)->withTrashed();
    }
}
