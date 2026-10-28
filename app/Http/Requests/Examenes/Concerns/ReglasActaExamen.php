<?php

namespace App\Http\Requests\Examenes\Concerns;

use App\Enums\EstadoCuenta;
use App\Enums\RolSistema;
use App\Services\Examenes\ActaExamenService as Svc;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Reglas comunes de P2 Registrar / Modificar Acta de Examen: Fecha posterior a hoy (CU 21 alt. 10.1, CU 22 alt. 12.1),
 * catálogos vigentes y Tribunal con al menos un Docente "Activo", sin repetir docente ni rol (E11-D3).
 */
trait ReglasActaExamen
{
    public const MENSAJE_TRIBUNAL_VACIO = 'Debe agregar al menos un docente al tribunal.';

    public const MENSAJE_DOCENTE_REPETIDO = 'Un docente no puede integrar el tribunal más de una vez.';

    public const MENSAJE_ROL_REPETIDO = 'Cada rol del tribunal puede asignarse una sola vez.';

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    protected function reglasActa(): array
    {
        return [
            'fecha_acta' => ['bail', 'required', 'date_format:d/m/Y', 'after:today'],
            'modalidad_id' => ['required', 'integer', Rule::exists('modalidades', 'id')->whereNull('deleted_at')],
            'llamado_id' => ['required', 'integer', Rule::exists('llamados', 'id')->whereNull('deleted_at')],
            'tipo_acta_id' => ['required', 'integer', Rule::exists('tipos_acta', 'id')->whereNull('deleted_at')],
            'tribunal' => ['required', 'array', 'min:1'],
            'tribunal.*.user_id' => ['required', 'integer', 'distinct', Rule::exists('users', 'id')
                ->where('estado_cuenta', EstadoCuenta::Activo->value)
                ->whereIn('id', fn ($q) => $q->select('model_id')->from('model_has_roles')
                    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                    ->where('roles.name', RolSistema::Docente->value))],
            'tribunal.*.rol_tribunal_id' => ['required', 'integer', 'distinct', Rule::exists('roles_tribunal', 'id')->whereNull('deleted_at')],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'fecha_acta.after' => Svc::MENSAJE_FECHA,
            'fecha_acta.*' => 'Debe ingresar una fecha de examen válida (dd/mm/aaaa).',
            'detalle_plan_estudio_id.*' => 'Debe seleccionar una materia del plan de estudio activo.',
            'modalidad_id.*' => 'Debe seleccionar una modalidad.',
            'llamado_id.*' => 'Debe seleccionar un llamado.',
            'tipo_acta_id.*' => 'Debe seleccionar un tipo de acta.',
            'tribunal.*' => self::MENSAJE_TRIBUNAL_VACIO,
            'tribunal.*.user_id.distinct' => self::MENSAJE_DOCENTE_REPETIDO,
            'tribunal.*.user_id.*' => 'Debe seleccionar un docente activo.',
            'tribunal.*.rol_tribunal_id.distinct' => self::MENSAJE_ROL_REPETIDO,
            'tribunal.*.rol_tribunal_id.*' => 'Debe seleccionar un rol del tribunal.',
        ];
    }

    /** Los errores de las filas del tribunal se muestran juntos bajo la tabla. */
    public function after(): array
    {
        return [function (Validator $validator) {
            $filas = collect($validator->errors()->getMessages())->filter(fn ($m, $clave) => str_starts_with($clave, 'tribunal.'));
            if ($filas->isNotEmpty() && ! $validator->errors()->has('tribunal')) {
                $validator->errors()->add('tribunal', $filas->flatten()->first());
            }
        }];
    }

    /** @return array<string, mixed> datos validados con la fecha en Y-m-d */
    public function datos(): array
    {
        $datos = $this->validated();
        $datos['fecha_acta'] = Carbon::createFromFormat('d/m/Y', $datos['fecha_acta'])->toDateString();

        return $datos;
    }
}
