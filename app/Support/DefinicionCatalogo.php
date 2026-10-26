<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * Un catálogo del ABM genérico (config/catalogos.php): modelo, campos, reglas y columnas de la tabla.
 */
final class DefinicionCatalogo
{
    /**
     * @param  class-string<Model>  $modelo
     * @param  array<string, array<string, mixed>>  $campos
     */
    private function __construct(
        public readonly string $clave,
        public readonly string $titulo,
        public readonly string $singular,
        public readonly string $modelo,
        public readonly array $campos,
    ) {}

    public static function buscar(string $clave): ?self
    {
        $config = config("catalogos.{$clave}");

        return $config ? new self($clave, $config['titulo'], $config['singular'], $config['modelo'], $config['campos']) : null;
    }

    /** @return list<self> */
    public static function todos(): array
    {
        return array_map(fn (string $clave) => self::buscar($clave), array_keys(config('catalogos')));
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

    /** @return array<string, string> opciones [id => texto] de un campo select (solo registros activos). */
    public function opciones(string $campo): array
    {
        $def = $this->campos[$campo];

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

    /** Columnas para <x-datatable>: un select muestra el texto de su relación. */
    public function columnasTabla(): array
    {
        $columnas = collect($this->campos)->map(fn (array $def, string $campo) => $this->tipo($campo) === 'select'
            ? ['data' => "{$campo}_texto", 'titulo' => $def['label'], 'orderable' => false, 'searchable' => false]
            : ['data' => $campo, 'titulo' => $def['label']]
        )->values()->all();

        return [
            ...$columnas,
            ['data' => 'estado', 'titulo' => 'Estado', 'orderable' => false, 'searchable' => false],
            ['data' => 'acciones', 'titulo' => 'Acciones', 'orderable' => false, 'searchable' => false, 'clase' => 'text-nowrap', 'prioridad' => 1],
        ];
    }

    public function indiceColumnaPrincipal(): int
    {
        return (int) array_search($this->columnaPrincipal(), array_keys($this->campos), true);
    }
}
