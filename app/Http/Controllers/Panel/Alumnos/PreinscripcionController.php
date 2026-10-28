<?php

namespace App\Http\Controllers\Panel\Alumnos;

use App\Enums\EstadoCivil;
use App\Enums\Idioma;
use App\Enums\NivelEstudios;
use App\Enums\Sexo;
use App\Enums\SituacionEstudios;
use App\Http\Controllers\Controller;
use App\Http\Requests\Alumnos\BuscarDniPreinscripcionRequest;
use App\Http\Requests\Alumnos\CarreraPreinscripcionRequest;
use App\Http\Requests\Alumnos\DatosPersonalesAlumnoRequest;
use App\Http\Requests\Alumnos\DatosPersonalesPreinscripcionRequest;
use App\Http\Requests\Alumnos\EncuestaSocioeconomicaRequest;
use App\Models\Carrera;
use App\Models\EncuestaSocioeconomica;
use App\Models\Localidad;
use App\Services\Alumnos\AsistentePreinscripcion;
use App\Services\Alumnos\PreinscripcionService;
use App\Services\Alumnos\UsuarioAlumnoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * CU1 Preinscripción: asistente de 5 pasos (P1 Buscar alumno, P2 Datos personales y P3 Encuesta del CU 9,
 * P4 Carrera y documentacion, P5 Confirmar). Navegación según el diagrama de estados (Fig. 61).
 */
class PreinscripcionController extends Controller
{
    public const ENCABEZADO = 'PREINSCRIPCIÓN';

    /** Claves flash de los modales de aviso (alt. 4.1 y 18.1). */
    public const AVISO_DNI_INEXISTENTE = 'aviso_dni_inexistente';

    public const AVISO_DOCUMENTACION = 'aviso_documentacion';

    public function __construct(
        private readonly AsistentePreinscripcion $asistente,
        private readonly PreinscripcionService $preinscripciones,
        private readonly UsuarioAlumnoService $usuariosAlumno,
    ) {}

    /** P1 (pasos 1–2). */
    public function create(): View
    {
        return view('panel.alumnos.preinscripcion.p1-buscar', ['dni' => $this->asistente->dni()]);
    }

    /** P1 "Buscar" (pasos 3–4): DNI existe → P4; no existe → P2 con "DNI Inexistente" (alt. 4.1). */
    public function buscar(BuscarDniPreinscripcionRequest $request): RedirectResponse
    {
        $dni = $request->validated('dni');
        $usuario = $this->preinscripciones->buscarUsuario($dni);
        $this->asistente->iniciar($dni, $usuario);

        return $usuario
            ? redirect()->route('panel.preinscripcion.carrera')
            : redirect()->route('panel.preinscripcion.datos-personales')->with(self::AVISO_DNI_INEXISTENTE, true);
    }

    /** P2 (CU 9 pasos 2–3). */
    public function datosPersonales(): View|RedirectResponse
    {
        if (! $this->asistente->puedeVer(2)) {
            return $this->volverAP1();
        }
        $datos = $this->asistente->datosPersonales() ?? [];
        $localidad = isset($datos['localidad_id']) ? Localidad::query()->find($datos['localidad_id']) : null;

        return view('panel.alumnos.preinscripcion.p2-datos-personales', [
            'dni' => $this->asistente->dni(),
            'datos' => $datos,
            'provinciaId' => $localidad?->provincia_id,
            'localidadId' => $localidad?->id,
            'foto' => $this->asistente->fotoTemporal(),
            'sexos' => Sexo::opciones(),
            'estadosCiviles' => EstadoCivil::opciones(),
        ]);
    }

    public function guardarDatosPersonales(DatosPersonalesPreinscripcionRequest $request): RedirectResponse
    {
        if (! $this->asistente->puedeVer(2)) {
            return $this->volverAP1();
        }
        $foto = $this->asistente->fotoTemporal();

        if ($request->hasFile('foto')) {
            $this->usuariosAlumno->descartarFotoTemporal($foto);
            $foto = $this->usuariosAlumno->guardarFotoTemporal($request->file('foto'));
        }

        $this->asistente->guardarDatosPersonales($request->datosSesion(), $foto);

        return redirect()->route('panel.preinscripcion.encuesta');
    }

    /** P3 (CU 9 pasos 4–5). */
    public function encuesta(): View|RedirectResponse
    {
        if (! $this->asistente->puedeVer(3)) {
            return $this->volverAP1();
        }

        return view('panel.alumnos.preinscripcion.p3-encuesta', [
            'encuesta' => $this->asistente->encuesta() ?? [],
            'niveles' => NivelEstudios::opciones(),
            'idiomas' => Idioma::opciones(),
        ]);
    }

    public function guardarEncuesta(EncuestaSocioeconomicaRequest $request): RedirectResponse
    {
        if (! $this->asistente->puedeVer(3)) {
            return $this->volverAP1();
        }
        $this->asistente->guardarEncuesta($request->validated());

        return redirect()->route('panel.preinscripcion.carrera');
    }

    /** P4 (pasos 8–21). */
    public function carrera(): View|RedirectResponse
    {
        if (! $this->asistente->puedeVer(4)) {
            return $this->volverAP1();
        }

        return view('panel.alumnos.preinscripcion.p4-carrera', [
            'carreras' => $this->preinscripciones->carrerasDisponibles()->pluck('nombre_carrera', 'id')->all(),
            'situaciones' => SituacionEstudios::opciones(),
            'seleccion' => $this->asistente->carrera(),
            'adjuntos' => $this->asistente->documentos(),
            'urlAtras' => route($this->asistente->esNuevo() ? 'panel.preinscripcion.encuesta' : 'panel.preinscripcion.create'),
        ]);
    }

    /** P4 "Continuar": guarda los PDF en temporales; si falta alguno, P5 muestra el aviso del alt. 18.1. */
    public function guardarCarrera(CarreraPreinscripcionRequest $request): RedirectResponse
    {
        if (! $this->asistente->puedeVer(4)) {
            return $this->volverAP1();
        }
        $documentos = $this->asistente->documentos();

        foreach (array_keys(PreinscripcionService::DOCUMENTOS) as $clave) {
            if ($request->hasFile($clave)) {
                $this->preinscripciones->descartarTemporales(null, array_filter([$documentos[$clave] ?? null]));
                $documentos[$clave] = $this->preinscripciones->guardarDocumentoTemporal($request->file($clave));
            }
        }
        $this->asistente->guardarCarrera($request->datosCarrera(), $documentos);

        $faltaDocumentacion = count(array_filter($documentos)) < count(PreinscripcionService::DOCUMENTOS);

        return redirect()->route('panel.preinscripcion.confirmar')->with(self::AVISO_DOCUMENTACION, $faltaDocumentacion);
    }

    /** P5 (pasos 22–23). */
    public function confirmar(): View|RedirectResponse
    {
        if (! $this->asistente->puedeVer(5)) {
            return $this->volverAP1();
        }
        $existente = $this->asistente->usuarioExistente();
        $seleccion = $this->asistente->carrera();

        if ($existente) {
            $existente->load(['localidad.provincia', 'encuesta']);
            $persona = $existente->only(['apellido_usuario', 'nombre_usuario', 'sexo', 'estado_civil', 'email', 'telefono_usuario', 'celular_usuario', 'direccion_usuario']);
            $persona['fecha_nacimiento'] = $existente->fecha_nacimiento?->format('Y-m-d');
            $localidad = $existente->localidad;
            $encuesta = $existente->encuesta?->only(EncuestaSocioeconomica::PREGUNTAS) ?? [];
        } else {
            $persona = DatosPersonalesAlumnoRequest::paraUsuario($this->asistente->datosPersonales());
            $localidad = Localidad::query()->with('provincia')->find($persona['localidad_id'] ?? null);
            $encuesta = EncuestaSocioeconomicaRequest::paraEncuesta($this->asistente->encuesta());
        }

        return view('panel.alumnos.preinscripcion.p5-confirmar', [
            'nuevo' => $existente === null,
            'dni' => $this->asistente->dni(),
            'persona' => $persona,
            'localidad' => $localidad,
            'encuesta' => $encuesta,
            'carrera' => Carrera::query()->find($seleccion['carrera_id']),
            'seleccion' => $seleccion,
            'constanciaTitulo' => ! empty($this->asistente->documentos()['documento_titulo']),
        ]);
    }

    /** P5 "Confirmar preinscripcion" (pasos 24–28). Vuelve a P1 con el mensaje de registración exitosa. */
    public function store(): RedirectResponse
    {
        if (! $this->asistente->puedeVer(5)) {
            return $this->volverAP1();
        }
        $existente = $this->asistente->usuarioExistente();
        $datos = $encuesta = null;

        if (! $existente) {
            $sesion = $this->asistente->datosPersonales();
            $validador = Validator::make($sesion, DatosPersonalesAlumnoRequest::reglas($sesion), DatosPersonalesAlumnoRequest::mensajesDatosUsuario());
            if ($validador->fails()) {
                return redirect()->route('panel.preinscripcion.datos-personales')->withErrors($validador);
            }
            $datos = DatosPersonalesAlumnoRequest::paraUsuario($sesion);
            $encuesta = EncuestaSocioeconomicaRequest::paraEncuesta($this->asistente->encuesta());
        }

        try {
            $this->preinscripciones->registrar($existente, $datos, $encuesta, $this->asistente->fotoTemporal(), $this->asistente->carrera(), $this->asistente->documentos());
        } catch (ValidationException $error) {
            $ruta = isset($error->errors()['carrera_id']) ? 'panel.preinscripcion.carrera' : 'panel.preinscripcion.datos-personales';

            return redirect()->route($ruta)->withErrors($error->errors());
        }

        $this->asistente->terminar();

        return redirect()->route('panel.preinscripcion.create')->with('success', PreinscripcionService::MENSAJE_REGISTRO_EXITOSO);
    }

    private function volverAP1(): RedirectResponse
    {
        return redirect()->route('panel.preinscripcion.create');
    }
}
