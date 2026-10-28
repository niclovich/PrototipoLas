<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * Un catálogo del ABM genérico (config/catalogos.php): modelo, campos, reglas, textos y columnas de la tabla.
 */
final class DefinicionCatalogo
{
    /**
     * @param  class-string<Model>  $modelo
     * @param  array<string, array<string, mixed>>  $campos
     * @param  array<string, string>  $bloqueanBaja  relación hija => mensaje si tiene registros activos
     */
    private function __construct(
        public readonly string $clave,
        public readonly string $titulo,
        public readonly string $singular,
        public readonly string $genero,
        public readonly string $modelo,
        public readonly array $campos,
        public readonly array $bloqueanBaja,
    ) {}

    public static function buscar(string $clave): ?self
    {
        $c = config("catalogos.{$clave}");

        return $c
            ? new self($clave, $c['titulo'], $c['singular'], $c['genero'] ?? 'm', $c['modelo'], $c['campos'], $c['bloquean_baja'] ?? [])
            : null;
    }

    /** @return list<self> */
    public static function todos(): array
    {
        return array_map(fn (string $clave) => self::buscar($clave), array_keys(config('catalogos')));
    }

    /** "Nuevo modalidad" → "Nueva modalidad" según el género del catálogo. */
    public function textoNuevo(): string
    {
        return ($this->genero === 'f' ? 'Nueva' : 'Nuevo').' '.mb_strtolower($this->singular);
    }

    /** Participio concordado: participio('registrad') → "registrado" / "registrada". */
    public function participio(string $raiz): string
    {
        return $raiz.($this->genero === 'f' ? 'a' : 'o');
    }

    public function tipo(string $campo): string
    {
        return $this->campos[$campo]['tipo'] ?? 'texto';
    }

    public function columnaPrincipal(): string
    {
        return collect($this->campos)->keys()->first(fn (string $campo) => $this->tipo($campo) === 'texto');
    }

    /** @return list<string> */
    public function relaciones(): array
    {
        return collect($this->campos)->pluck('relacion')->filter()->values()->all();
    }

    public function consulta(): Builder
    {
        return $this->modelo::query()->with($this->relaciones());
    }

    public function nuevo(): Model
    {
        return new $this->modelo;
    }

    /** @return array<string, string> opciones de un campo select (registros activos) o lista (valores fijos del modelo). */
    public function opciones(string $campo): array
    {
        $def = $this->campos[$campo];

        if ($this->tipo($campo) === 'lista') {
            return array_combine($def['valores'], $def['valores']);
        }

        return $def['modelo']::query()->orderBy($def['texto'])->pluck($def['texto'], 'id')->all();
    }

    /** @return array<string, list<mixed>> */
    public function reglas(?Model $registro, array $datos): array
    {
        $tabla = $this->nuevo()->getTable();

        return collect($this->campos)->mapWithKeys(function (array $def, string $campo) use ($tabla, $registro, $datos) {
            $reglas = match ($this->tipo($campo)) {
                'numero' => ['required', 'integer', 'min:'.($def['min'] ?? 0), 'max:'.($def['max'] ?? 65535)],
                'select' => ['required', 'integer', Rule::exists((new $def['modelo'])->getTable(), 'id')->whereNull('deleted_at')],
                'lista' => ['required', 'string', Rule::in($def['valores'])],
                default => ['required', 'string', 'max:'.($def['max'] ?? 255)],
            };

            if ($def['unico'] ?? false) {
                $unico = Rule::unique($tabla, $campo)->ignore($registro?->getKey());
                foreach ($def['unico_con'] ?? [] as $otro) {
                    $unico->where($otro, $datos[$otro] ?? null);
                }
                $reglas[] = $unico;
            }

            return [$campo => $reglas];
        })->all();
    }

    /** @return array<string, string> */
    public function etiquetas(): array
    {
        return collect($this->campos)->map(fn (array $def) => mb_strtolower($def['label']))->all();
    }

    /**
     * Columnas para <x-datatable>: un select muestra el texto de su relación. En celular quedan visibles
     * la columna principal (prioridad 1) y las acciones (prioridad 2).
     */
    public function columnasTabla(): array
    {
        $principal = $this->columnaPrincipal();

        $columnas = collect($this->campos)->map(fn (array $def, string $campo) => $this->tipo($campo) === 'select'
            ? ['data' => "{$campo}_texto", 'titulo' => $def['label'], 'orderable' => false, 'searchable' => false, 'prioridad' => 3]
            : ['data' => $campo, 'titulo' => $def['label'], 'prioridad' => $campo === $principal ? 1 : 3]
        )->values()->all();

        return [
            ...$columnas,
            ['data' => 'estado', 'titulo' => 'Estado', 'orderable' => false, 'searchable' => false, 'prioridad' => 4],
            ['data' => 'acciones', 'titulo' => 'Acciones', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 2],
        ];
    }

    public function indiceColumnaPrincipal(): int
    {
        return (int) array_search($this->columnaPrincipal(), array_keys($this->campos), true);
    }
}
