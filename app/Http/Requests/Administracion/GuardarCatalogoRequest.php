<?php

namespace App\Http\Requests\Administracion;

use App\Support\DefinicionCatalogo;
use Illuminate\Foundation\Http\FormRequest;

/** Reglas armadas desde config/catalogos.php para el catálogo de la ruta. */
class GuardarCatalogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->catalogo()->reglas($this->route('registro'), $this->all());
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['*.unique' => 'Ya existe un registro con ese valor (revise también la pestaña Eliminados).'];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return $this->catalogo()->etiquetas();
    }

    private function catalogo(): DefinicionCatalogo
    {
        return $this->route('catalogo');
    }
}
