<?php

namespace App\Models;

use App\Enums\EstadoTramite;
use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Clase TituloTramite (Hoja 3): CU6 lo crea en "Sin Gestión"; Alumno 1..1 — 0..1. */
class TituloTramite extends Model
{
    use AuditaCambios, HasFactory;

    protected $table = 'titulos_tramite';

    protected $primaryKey = 'id';

    protected $fillable = [
        'alumno_id', 'tipo_gestion_titulo_id', 'fecha_creacion', 'fecha_inicio', 'fecha_recepcion',
        'fecha_retiro', 'estado_tramite', 'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'date',
            'fecha_inicio' => 'date',
            'fecha_recepcion' => 'date',
            'fecha_retiro' => 'date',
            'estado_tramite' => EstadoTramite::class,
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function tipoGestion(): BelongsTo
    {
        return $this->belongsTo(TipoGestionTitulo::class, 'tipo_gestion_titulo_id')->withTrashed();
    }
}
