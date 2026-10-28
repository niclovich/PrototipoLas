<?php

namespace Tests\Feature\Alumnos;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * Valida un FormRequest sin ruta (E3 no tiene pantallas): devuelve los errores o [] si es válido.
 */
trait ValidaRequests
{
    /**
     * @param  class-string<FormRequest>  $clase
     * @return array<string, list<string>>
     */
    private function errores(string $clase, array $datos, ?User $usuario = null, array $archivos = []): array
    {
        return $this->resolver($clase, $datos, $usuario, $archivos)[1];
    }

    /**
     * @param  class-string<FormRequest>  $clase
     * @return array{0: ?FormRequest, 1: array<string, list<string>>}
     */
    private function resolver(string $clase, array $datos, ?User $usuario = null, array $archivos = []): array
    {
        $usuario ??= $this->preceptor;
        $request = $clase::create('/', 'POST', $datos, [], $archivos);
        $request->setContainer(app())->setRedirector(app('redirect'));
        $request->setUserResolver(fn () => $usuario);

        try {
            $request->validateResolved();

            return [$request, []];
        } catch (ValidationException $e) {
            return [null, $e->errors()];
        }
    }
}
