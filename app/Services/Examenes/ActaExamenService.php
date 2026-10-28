<?php

namespace App\Services\Examenes;

use App\Enums\Asentada;
use App\Enums\EstadoActa;
use App\Enums\EstadoCuenta;
use App\Enums\EstadoGeneral;
use App\Exceptions\OperacionNoPermitida;
use App\Models\ActaExamen;
use App\Models\Carrera;
use App\Models\DetallePlanEstudio;
use App\Models\IntegranteTribunal;
use App\Models\Llamado;
use App\Models\Modalidad;
use App\Models\RolTribunal;
use App\Models\TipoActa;
use App\Models\Tribunal;
use App\Models\User;
use App\Notifications\TribunalActaExamen;
use App\Services\Numeracion\NumeradorService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * CU 21 Registrar y CU 22 Modificar datos de Acta de Examen (Coordinador de Carrera).
 */
class ActaExamenService
{
    /** CU 21 alt. 10.1, CU 22 alt. 12.1 y 8.1. */
    public const MENSAJE_FECHA = 'La fecha debe ser superior a la fecha actual.';

    public const MENSAJE_CON_INSCRIPTOS = 'El Acta de Examen no puede modificarse porque posee Alumnos inscriptos';

    public const CONFIRMACION_REGISTRO = 'Acta de Examen registrada exitosamente';

    public const CONFIRMACION_REGISTRO_TEXTO = 'Se generó el número de acta y quedó disponible para el asentamiento de las notas.';

    public const CONFIRMACION_MODIFICACION = 'Acta de Examen modificada exitosamente';

    public function __construct(private readonly NumeradorService $numerador) {}

    /** @return Collection<int, Carrera> paso 2: carreras "Activo". */
    public function carreras(): Collection
    {
        return Carrera::query()->activas()->orderBy('nombre_carrera')->get(['id', 'nombre_carrera']);
    }

    /** @return array<int, string> paso 5: materias "Activo" del plan activo de la carrera (id del DetallePlanEstudio => nombre). */
    public function materias(Carrera $carrera): array
    {
        return $this->detallesDe($carrera)->with('materia')->get()
            ->sortBy(fn (DetallePlanEstudio $d) => [$d->anio_curso_det_plan_estudio, $d->materia->nombre_materia])
            ->mapWithKeys(fn (DetallePlanEstudio $d) => [$d->id => $d->materia->nombre_materia])
            ->all();
    }

    /** Query de los DetallePlanEstudio elegibles de la carrera (lo usa también la validación). */
    public function detallesDe(Carrera $carrera)
    {
        return DetallePlanEstudio::query()->activos()
            ->whereHas('planEstudio', fn ($plan) => $plan->activos()->where('carrera_id', $carrera->id));
    }

    /** @return array<string, array<int|string, string>> combos de P2 */
    public function opciones(): array
    {
        return [
            'modalidades' => Modalidad::query()->orderBy('modalidad')->pluck('modalidad', 'id')->all(),
            'llamados' => Llamado::query()->orderBy('id')->pluck('llamado', 'id')->all(),
            'tiposActa' => TipoActa::query()->orderBy('id')->pluck('descripcion', 'id')->all(),
            'roles' => RolTribunal::query()->orderBy('id')->pluck('descripcion', 'id')->all(),
            'docentes' => $this->docentes()->mapWithKeys(fn (User $u) => [$u->id => "{$u->nombre_completo} · DNI {$u->dni_usuario}"])->all(),
            'nombresDocentes' => $this->docentes()->pluck('nombre_completo', 'id')->all(),
        ];
    }

    /** @return Collection<int, User> paso 20 (§7-15): Docentes con cuenta "Activo". */
    public function docentes(): Collection
    {
        return User::query()->docentes()->conEstadoCuenta(EstadoCuenta::Activo)
            ->orderBy('apellido_usuario')->orderBy('nombre_usuario')
            ->get(['id', 'apellido_usuario', 'nombre_usuario', 'dni_usuario']);
    }

    /**
     * CU 21 pasos 29–32: genera el número, registra el acta "Pendiente" (asentada N), el Tribunal y sus integrantes.
     *
     * @param  array{detalle_plan_estudio_id: int, fecha_acta: string, modalidad_id: int, llamado_id: int, tipo_acta_id: int, tribunal: list<array{user_id: int, rol_tribunal_id: int}>}  $datos
     */
    public function registrar(array $datos): ActaExamen
    {
        $acta = DB::transaction(function () use ($datos) {
            $tribunal = Tribunal::create();
            $acta = new ActaExamen([
                'nro_acta' => $this->numerador->siguiente('actas_examen', 'nro_acta'),
                'fecha_acta' => $datos['fecha_acta'],
                'estado_acta' => EstadoActa::Pendiente,
                'asentada' => Asentada::No,
            ]);
            $acta->detallePlan()->associate($datos['detalle_plan_estudio_id']);
            $acta->modalidad()->associate($datos['modalidad_id']);
            $acta->llamado()->associate($datos['llamado_id']);
            $acta->tipoActa()->associate($datos['tipo_acta_id']);
            $acta->tribunal()->associate($tribunal);
            $acta->save();

            foreach ($datos['tribunal'] as $integrante) {
                $this->vincular($tribunal, $integrante);
            }

            return $acta;
        });

        Notification::send($this->integrantesActivos($acta)->pluck('docente'), new TribunalActaExamen($acta));

        return $acta;
    }

    /** CU 22 paso 8 (alt. 8.1) y precondición "Pendiente". */
    public function asegurarModificable(ActaExamen $acta): void
    {
        abort_unless($acta->estado_acta === EstadoActa::Pendiente, 404);

        if ($acta->inscriptos()->exists()) {
            throw new OperacionNoPermitida(self::MENSAJE_CON_INSCRIPTOS);
        }
    }

    /**
     * CU 22 pasos 31–33: actualiza fecha, modalidad, llamado y tipo; vincula los docentes agregados y desvincula
     * (Inactivo) los quitados; notifica al tribunal y a los desvinculados.
     *
     * @param  array<string, mixed>  $datos
     */
    public function modificar(ActaExamen $acta, array $datos): ActaExamen
    {
        $desvinculados = DB::transaction(function () use (&$acta, $datos) {
            $acta = ActaExamen::query()->lockForUpdate()->findOrFail($acta->getKey());
            $this->asegurarModificable($acta);

            $acta->fecha_acta = $datos['fecha_acta'];
            $acta->modalidad()->associate($datos['modalidad_id']);
            $acta->llamado()->associate($datos['llamado_id']);
            $acta->tipoActa()->associate($datos['tipo_acta_id']);
            $acta->save();

            $clave = fn ($userId, $rolId) => "{$userId}-{$rolId}";
            $pedidos = collect($datos['tribunal'])->keyBy(fn ($i) => $clave($i['user_id'], $i['rol_tribunal_id']));
            $actuales = $this->integrantesActivos($acta);
            $quitados = $actuales->reject(fn (IntegranteTribunal $i) => $pedidos->has($clave($i->user_id, $i->rol_tribunal_id)));

            foreach ($quitados as $integrante) {
                $integrante->update(['estado_integrante' => EstadoGeneral::Inactivo]);
            }
            $vigentes = $actuales->map(fn (IntegranteTribunal $i) => $clave($i->user_id, $i->rol_tribunal_id));
            foreach ($pedidos->except($vigentes->all()) as $nuevo) {
                $this->vincular($acta->tribunal, $nuevo);
            }

            $siguen = collect($datos['tribunal'])->pluck('user_id')->map(fn ($id) => (int) $id);

            return $quitados->pluck('docente')->reject(fn (User $u) => $siguen->contains($u->id))->unique('id');
        });

        Notification::send($this->integrantesActivos($acta)->pluck('docente'), new TribunalActaExamen($acta));
        Notification::send($desvinculados, new TribunalActaExamen($acta, TribunalActaExamen::DESVINCULADO));

        return $acta;
    }

    /** @return Collection<int, IntegranteTribunal> */
    public function integrantesActivos(ActaExamen $acta): Collection
    {
        return IntegranteTribunal::query()
            ->where('tribunal_id', $acta->tribunal_id)
            ->where('estado_integrante', EstadoGeneral::Activo->value)
            ->with(['docente', 'rolTribunal'])
            ->orderBy('rol_tribunal_id')
            ->get();
    }

    /** @return list<array{user_id: int, rol_tribunal_id: int}> tribunal actual para precargar P2 Modificar */
    public function tribunalActual(ActaExamen $acta): array
    {
        return $this->integrantesActivos($acta)
            ->map(fn (IntegranteTribunal $i) => ['user_id' => $i->user_id, 'rol_tribunal_id' => $i->rol_tribunal_id])
            ->all();
    }

    /** @return list<string> "Detalle del Registro" de P3 (P3.png) */
    public function lineasConfirmacion(ActaExamen $acta): array
    {
        $acta->loadMissing(['detallePlan.materia', 'modalidad', 'llamado', 'tipoActa']);
        $tribunal = $this->integrantesActivos($acta)
            ->map(fn (IntegranteTribunal $i) => "{$i->docente->apellido_usuario} ({$i->rolTribunal->descripcion})")
            ->implode(', ');

        return [
            "Nro Acta: {$acta->nro_acta_texto} · Materia: {$acta->detallePlan->materia->nombre_materia}",
            "Modalidad: {$acta->modalidad->modalidad} · Llamado: {$acta->llamado->llamado} · Tipo: {$acta->tipoActa->descripcion}",
            "Fecha: {$acta->fecha_acta->format('d/m/Y')} · Estado: {$acta->estado_acta->label()} · Asentada: {$acta->asentada->label()}",
            "Tribunal: {$tribunal}",
        ];
    }

    /** @param  array{user_id: int|string, rol_tribunal_id: int|string}  $integrante */
    private function vincular(Tribunal $tribunal, array $integrante): void
    {
        IntegranteTribunal::create([
            'tribunal_id' => $tribunal->id,
            'user_id' => $integrante['user_id'],
            'rol_tribunal_id' => $integrante['rol_tribunal_id'],
            'estado_integrante' => EstadoGeneral::Activo,
        ]);
    }
}
