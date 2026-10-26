<?php

namespace App\Enums\Concerns;

/**
 * Utilidades comunes a los Enums de estados/valores (§3 de la skill).
 * Cada Enum que lo usa define label() y badge().
 */
trait EnumConEtiqueta
{
    /** @return list<string> */
    public static function valores(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return array<string, string> valor => etiqueta, para selects */
    public static function opciones(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $caso) => [$caso->value => $caso->label()])
            ->all();
    }

    public function label(): string
    {
        return $this->value;
    }
}
