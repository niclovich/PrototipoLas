<?php

namespace App\Http\Controllers\Panel\Personal;

use App\DataTables\PersonalDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Personal\ModificarPersonalRequest;
use App\Http\Requests\Personal\RegistrarBajaPersonalRequest;
use App\Http\Requests\Personal\RegistrarPersonalRequest;
use App\Http\Requests\Personal\VerificarDniRequest;
use App\Models\User;
use App\Services\Personal\PersonalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Gestión de Personal (Director General): P1 (entrada de los CU 25, 26 y 27), P2 Registrar,
 * P2 Modificar, modal de Baja y las Confirmaciones.
 */
class PersonalController extends Controller
{
    public function __construct(private readonly PersonalService $personal) {}

    /** P1 Gestión de Personal. Si la baja volvió con errores, el modal se reabre con el mismo usuario. */
    public function index(Request $request): View
    {
        $idBaja = $request->old('usuario_id');

        return view('panel.personal.index', [
            'roles' => $this->personal->rolesDelPersonal(),
            'motivos' => $this->personal->motivosDeBaja(),
            'usuarioBaja' => $idBaja
                ? User::query()->personalActivo()->with('roles')->find($idBaja)
                : null,
        ]);
    }

    public function datatable(Request $request, PersonalDataTable $tabla): JsonResponse
    {
        return $tabla->respuesta($request);
    }

    /** CU 25 pasos 1–14: P2 Registrar Personal. */
    public function create(): View
    {
        return view('panel.personal.registrar', $this->datosFormulario(new User));
    }

    /** CU 25 paso 4: el DNI ya está registrado (AJAX al ingresarlo). */
    public function verificarDni(VerificarDniRequest $request): JsonResponse
    {
        return response()->json(['existe' => $this->personal->dniRegistrado($request->validated('dni_usuario'))]);
    }

    /** CU 25 pasos 18–21. */
    public function store(RegistrarPersonalRequest $request): RedirectResponse
    {
        $this->personal->registrar($request->datosUsuario(), (int) $request->validated('rol_id'), $request->validated('password'));

        return $this->confirmar(PersonalService::CONFIRMACION_REGISTRO);
    }

    /** CU 26 pasos 3–5: P2 Modificar con los datos actuales. */
    public function edit(User $personal): View
    {
        return view('panel.personal.modificar', $this->datosFormulario($personal->load(['roles', 'localidad.provincia'])));
    }

    /** CU 26 pasos 17–19. */
    public function update(ModificarPersonalRequest $request, User $personal): RedirectResponse
    {
        $this->personal->modificar($personal, $request->datosUsuario(), (int) $request->validated('rol_id'), $request->user());

        return $this->confirmar(PersonalService::CONFIRMACION_MODIFICACION);
    }

    /** CU 27 pasos 13–16. */
    public function registrarBaja(RegistrarBajaPersonalRequest $request, User $personal): RedirectResponse
    {
        $this->personal->registrarBaja($personal, (int) $request->validated('motivo_id'), $request->validated('observacion_novedad'), $request->user());

        return $this->confirmar(PersonalService::CONFIRMACION_BAJA);
    }

    /** P5 (Registrar) / P3 (Modificar y Baja) Confirmación: "Aceptar" vuelve a P1. */
    public function confirmacion(Request $request): View|RedirectResponse
    {
        $mensaje = $request->session()->get('confirmacion');

        return $mensaje
            ? view('panel.personal.confirmacion', ['mensaje' => $mensaje])
            : redirect()->route('panel.personal.index');
    }

    private function confirmar(string $mensaje): RedirectResponse
    {
        return redirect()->route('panel.personal.confirmacion')->with('confirmacion', $mensaje);
    }

    /** @return array<string, mixed> */
    private function datosFormulario(User $usuario): array
    {
        return [
            'usuario' => $usuario,
            'roles' => $this->personal->rolesDelPersonal(),
        ];
    }
}
