<?php

namespace App\Http\Requests\Administracion;

use App\Models\Rol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Rol|null $rol */
        $rol = $this->route('rol');

        return [
            'name' => [
                Rule::requiredIf(! $rol?->esDelSistema()),
                'string',
                'max:100',
                Rule::unique(Rol::class, 'name')->ignore($rol?->getKey()),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'permisos' => ['array'],
            'permisos.*' => ['string', Rule::in(config('permisos.permisos'))],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['name.unique' => 'Ya existe un rol con ese nombre.'];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['name' => 'nombre del rol', 'descripcion' => 'descripción', 'permisos.*' => 'permiso'];
    }

    /** @return array{name?: string, descripcion: ?string} */
    public function datosRol(): array
    {
        return $this->safe()->only(['name', 'descripcion']);
    }

    /** @return list<string> */
    public function permisos(): array
    {
        return array_values($this->validated('permisos', []));
    }
}
