<?php

namespace App\Models;

use App\Models\Concerns\AuditaCambios;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Clase Mes (Hoja 4): datos precargados sin CU propio. Cuota 0..* "corresponde a" 1..1 Mes.
 * La cuota de inscripción se identifica por su Mes "Inscripción" (nunca por un número fijo ni texto libre).
 */
class Mes extends Model
{
    use AuditaCambios;

    public const INSCRIPCION = 'Inscripción';

    /** Valores precargados en orden (el id es el orden) => mes del calendario en que vence (null = sin vencimiento mensual). */
    public const VALORES = [
        self::INSCRIPCION => null,
        'Marzo' => 3,
        'Abril' => 4,
        'Mayo' => 5,
        'Junio' => 6,
        'Julio' => 7,
        'Agosto' => 8,
        'Septiembre' => 9,
        'Octubre' => 10,
        'Noviembre' => 11,
        'Diciembre' => 12,
    ];

    protected $table = 'meses';

    protected $primaryKey = 'id';

    protected $fillable = ['descripcion'];

    /**
     * Crea (o completa) los valores precargados respetando id = orden. Lo usan CatalogosSeeder y CuotaFactory
     * (con o sin seeder previo); no hay MesFactory para no romper ese supuesto.
     *
     * @return Collection<int, self>
     */
    public static function precargados(): Collection
    {
        if (static::count() !== count(self::VALORES)) {
            foreach (array_keys(self::VALORES) as $orden => $descripcion) {
                static::updateOrCreate(['id' => $orden + 1], ['descripcion' => $descripcion]);
            }
        }

        return static::orderBy('id')->get();
    }

    public static function inscripcion(): self
    {
        return static::where('descripcion', self::INSCRIPCION)->firstOrFail();
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class);
    }

    public function esInscripcion(): bool
    {
        return $this->descripcion === self::INSCRIPCION;
    }

    /** Mes del calendario en que vence la cuota; null para Inscripción. */
    public function numeroCalendario(): ?int
    {
        return self::VALORES[$this->descripcion] ?? null;
    }
}
