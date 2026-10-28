<?php

namespace App\View\Components;

use App\Models\Localidad;
use App\Models\Provincia;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Selects dependientes Provincia → Localidad (Personal P2, CU1 P2). Al cambiar la Provincia,
 * resources/js/provincia-localidad.js pide sus Localidades a la ruta del módulo. El slot agrega campos al final
 * de la misma fila (Barrio en CU1 P2); `columna` es la clase de cada select.
 */
class SelectProvinciaLocalidad extends Component
{
    /** @var array<int, string> */
    public array $provincias;

    /** @var array<int, string> */
    public array $localidades;

    public ?string $provinciaId;

    public function __construct(
        public string $urlLocalidades,
        ?int $provincia = null,
        public ?int $localidad = null,
        public bool $requerido = true,
        public string $columna = 'col-12 col-md-6',
    ) {
        $this->provinciaId = (string) (old('provincia_id', $provincia) ?? '') ?: null;
        $this->provincias = Provincia::query()->orderBy('provincia')->pluck('provincia', 'id')->all();
        $this->localidades = $this->provinciaId
            ? Localidad::query()->where('provincia_id', $this->provinciaId)->orderBy('localidad')->pluck('localidad', 'id')->all()
            : [];
    }

    public function render(): View
    {
        return view('components.select-provincia-localidad');
    }
}
