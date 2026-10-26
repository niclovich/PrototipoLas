<?php

namespace Database\Seeders;

use App\Enums\CondicionCorrelativa;
use App\Enums\EstadoAlumno;
use App\Enums\EstadoCaja;
use App\Enums\EstadoCarrera;
use App\Enums\EstadoCuenta;
use App\Enums\EstadoGeneral;
use App\Enums\EstadoLegajo;
use App\Enums\RolSistema;
use App\Enums\SituacionEstudios;
use App\Models\ActaExamen;
use App\Models\Alumno;
use App\Models\AlumnoComision;
use App\Models\Beca;
use App\Models\Caja;
use App\Models\Carrera;
use App\Models\Comision;
use App\Models\ConvocatoriaBeca;
use App\Models\Correlativa;
use App\Models\Cuatrimestre;
use App\Models\Cuota;
use App\Models\DetallePagoCuota;
use App\Models\DetallePlanEstudio;
use App\Models\DetComision;
use App\Models\Duracion;
use App\Models\FacturaComprobante;
use App\Models\IntegranteTribunal;
use App\Models\Llamado;
use App\Models\Localidad;
use App\Models\Materia;
use App\Models\MedioPago;
use App\Models\Mes;
use App\Models\Modalidad;
use App\Models\Pago;
use App\Models\PlanEstudio;
use App\Models\Regimen;
use App\Models\RolTribunal;
use App\Models\SolicitudBeca;
use App\Models\TipoActa;
use App\Models\TipoBeca;
use App\Models\Tribunal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Datos de demostración (solo APP_ENV=local): un usuario por rol, una carrera Activo con plan,
 * materias, correlativas, comisiones, actas, cuotas (una por Mes), una caja abierta, un alumno con legajo, su Inscripción pagada y una beca.
 * Contraseña de todos los usuarios demo: Demo1234. Requiere CatalogosSeeder y RolesPermisosSeeder.
 */
class DemoSeeder extends Seeder
{
    public const CONTRASENIA = 'Demo1234';

    private const CARRERA = 'Tecnicatura Superior en Desarrollo de Software';

    private const MATERIAS = [
        1 => ['Programación I', 'Matemática', 'Sistemas Operativos', 'Inglés Técnico I'],
        2 => ['Programación II', 'Base de Datos I', 'Análisis de Sistemas', 'Inglés Técnico II'],
        3 => ['Programación III', 'Base de Datos II', 'Ingeniería de Software', 'Práctica Profesional'],
    ];

    public function run(): void
    {
        if (Carrera::where('nombre_carrera', self::CARRERA)->exists()) {
            return;
        }

        DB::transaction(function () {
            $localidad = Localidad::where('localidad', 'Salta')->firstOrFail();
            $usuarios = $this->usuarios($localidad);
            $carrera = Carrera::create([
                'nombre_carrera' => self::CARRERA,
                'resolucion_carrera' => '1234/2020',
                'estado_carrera' => EstadoCarrera::Activo,
                'modalidad_id' => Modalidad::where('modalidad', 'Presencial')->value('id'),
                'duracion_id' => Duracion::where('cantidad_anios', 3)->value('id'),
            ]);
            $plan = PlanEstudio::create(['carrera_id' => $carrera->id, 'anio_plan_estudio' => 2024, 'estado_plan_estudio' => EstadoGeneral::Activo]);
            $detalles = $this->materiasDelPlan($plan);
            $this->correlativas($detalles);
            $comisiones = $this->comisiones($carrera, $detalles);
            $this->actas($detalles[1], $usuarios[RolSistema::Docente->value]);
            $this->cuotas($carrera);

            $caja = Caja::create([
                'user_id' => $usuarios[RolSistema::Cajero->value]->id,
                'fecha_apertura' => now()->setTime(8, 0),
                'monto_inicial' => 10000,
                'estado_caja' => EstadoCaja::Abierta,
            ]);

            $this->alumnos($usuarios[RolSistema::Alumno->value], $carrera, $plan, $comisiones[1], $localidad);
            $this->pagoInscripcion($caja, $carrera);
        });
    }

    /** @return array<string, User> */
    private function usuarios(Localidad $localidad): array
    {
        $datos = [
            RolSistema::DirectorGeneral->value => ['director@las.test', 'Fernández', 'Laura', '20111111'],
            RolSistema::Coordinador->value => ['coordinador@las.test', 'Gómez', 'Martín', '20222222'],
            RolSistema::Preceptor->value => ['preceptor@las.test', 'López', 'Carla', '20333333'],
            RolSistema::AuxiliarAdministrativo->value => ['auxiliar@las.test', 'Martínez', 'Diego', '20444444'],
            RolSistema::Docente->value => ['docente@las.test', 'Rodríguez', 'Sofía', '20555555'],
            RolSistema::Cajero->value => ['cajero@las.test', 'Sánchez', 'Pablo', '20666666'],
            RolSistema::Alumno->value => ['alumno@las.test', 'Pérez', 'Juan', '40777777'],
        ];

        return collect($datos)->map(function (array $d, string $rol) use ($localidad) {
            [$email, $apellido, $nombre, $dni] = $d;
            $usuario = User::factory()->create([
                'email' => $email,
                'apellido_usuario' => $apellido,
                'nombre_usuario' => $nombre,
                'dni_usuario' => $dni,
                'localidad_id' => $localidad->id,
                'password' => self::CONTRASENIA,
                'estado_cuenta' => EstadoCuenta::Activo,
            ]);
            $usuario->syncRoles([$rol]);

            return $usuario;
        })->all();
    }

    /** @return array<int, list<DetallePlanEstudio>> año de cursado => detalles */
    private function materiasDelPlan(PlanEstudio $plan): array
    {
        $cuatrimestral = Regimen::where('regimen', 'Cuatrimestral')->value('id');
        $cuatrimestres = Cuatrimestre::whereIn('cuatrimestre', ['1er Cuatrimestre', '2do Cuatrimestre'])->orderBy('id')->pluck('id')->all();
        $detalles = [];

        foreach (self::MATERIAS as $anio => $nombres) {
            foreach ($nombres as $i => $nombre) {
                $materia = Materia::create(['nombre_materia' => $nombre, 'estado_materia' => EstadoGeneral::Activo]);
                $detalles[$anio][] = DetallePlanEstudio::create([
                    'plan_estudio_id' => $plan->id,
                    'materia_id' => $materia->id,
                    'regimen_id' => $cuatrimestral,
                    'cuatrimestre_id' => $cuatrimestres[$i % 2],
                    'codigo_det_plan_estudio' => sprintf('DS-%d%02d', $anio, $i + 1),
                    'carga_horaria_det_plan_estudio' => 64,
                    'anio_curso_det_plan_estudio' => $anio,
                    'estado_det_plan_estudio' => EstadoGeneral::Activo,
                ]);
            }
        }

        return $detalles;
    }

    /** Cada materia de 2.º y 3.º exige la del año anterior en la misma posición. */
    private function correlativas(array $detalles): void
    {
        foreach ([2, 3] as $anio) {
            foreach ($detalles[$anio] as $i => $detalle) {
                Correlativa::create([
                    'detalle_plan_estudio_id' => $detalle->id,
                    'detalle_plan_correlativa_id' => $detalles[$anio - 1][$i]->id,
                    'p_cursar' => CondicionCorrelativa::Regular,
                    'p_rendir' => CondicionCorrelativa::Aprobada,
                ]);
            }
        }
    }

    /** @return array<int, Comision> */
    private function comisiones(Carrera $carrera, array $detalles): array
    {
        $comisiones = [];
        foreach ($detalles as $anio => $delAnio) {
            $comisiones[$anio] = Comision::create([
                'carrera_id' => $carrera->id,
                'modalidad_id' => $carrera->modalidad_id,
                'codigo_comision' => "{$anio}A",
                'capacidad_comision' => 40,
                'carga_horaria_comision' => collect($delAnio)->sum('carga_horaria_det_plan_estudio'),
                'horario_comision' => 'Lunes a viernes de 18:00 a 22:00',
                'anio_curso' => $anio,
                'anio_lectivo' => (int) now()->format('Y'),
                'estado_comision' => EstadoGeneral::Activo,
            ]);
            foreach ($delAnio as $detalle) {
                DetComision::create(['comision_id' => $comisiones[$anio]->id, 'detalle_plan_estudio_id' => $detalle->id, 'estado_det_comision' => EstadoGeneral::Activo]);
            }
        }

        return $comisiones;
    }

    private function actas(array $detallesPrimerAnio, User $docente): void
    {
        $presidente = RolTribunal::where('descripcion', 'Presidente')->value('id');
        foreach ($detallesPrimerAnio as $i => $detalle) {
            $tribunal = Tribunal::create();
            IntegranteTribunal::create(['tribunal_id' => $tribunal->id, 'user_id' => $docente->id, 'rol_tribunal_id' => $presidente, 'estado_integrante' => EstadoGeneral::Activo]);
            ActaExamen::create([
                'nro_acta' => $i + 1,
                'detalle_plan_estudio_id' => $detalle->id,
                'modalidad_id' => Modalidad::where('modalidad', 'Presencial')->value('id'),
                'llamado_id' => Llamado::orderBy('id')->value('id'),
                'tipo_acta_id' => TipoActa::where('descripcion', 'Regular')->value('id'),
                'tribunal_id' => $tribunal->id,
                'fecha_acta' => today()->addWeeks(2 + $i),
            ]);
        }
    }

    /** Una cuota por cada Mes (Inscripción y Marzo a Diciembre) del año actual (el wireframe de CU7 muestra $ 134.000). */
    private function cuotas(Carrera $carrera): void
    {
        foreach (Mes::orderBy('id')->get() as $mes) {
            Cuota::create([
                'carrera_id' => $carrera->id,
                'mes_id' => $mes->id,
                'monto' => 134000,
                'anio' => (int) now()->format('Y'),
                'estado_cuota' => EstadoGeneral::Activo,
            ]);
        }
    }

    /** El alumno inscripto pagó su Cuota de Inscripción (requisito de CU2). */
    private function pagoInscripcion(Caja $caja, Carrera $carrera): void
    {
        $alumno = Alumno::where('nro_legajo', config('las.legajo_inicial'))->firstOrFail();
        $cuota = Cuota::deInscripcion()->where('carrera_id', $carrera->id)->where('anio', (int) now()->format('Y'))->firstOrFail();

        $pago = Pago::create([
            'alumno_id' => $alumno->id,
            'caja_id' => $caja->id,
            'medio_pago_id' => MedioPago::where('medio_pago', 'Efectivo')->value('id'),
            'fecha_pago' => now()->setTime(9, 0),
            'monto_total' => $cuota->monto,
            'estado_pago' => EstadoGeneral::Activo,
        ]);
        DetallePagoCuota::create(['pago_id' => $pago->id, 'cuota_id' => $cuota->id, 'monto_aplicado' => $cuota->monto]);
        FacturaComprobante::factory()->create(['pago_id' => $pago->id, 'numero_comprobante' => 1]);
    }

    private function alumnos(User $usuarioAlumno, Carrera $carrera, PlanEstudio $plan, Comision $comision, Localidad $localidad): void
    {
        $alumno = Alumno::create([
            'user_id' => $usuarioAlumno->id,
            'carrera_id' => $carrera->id,
            'plan_estudio_id' => $plan->id,
            'comision_actual_id' => $comision->id,
            'nro_legajo' => config('las.legajo_inicial'),
            'estado_alumno' => EstadoAlumno::Inscripto,
            'estado_legajo' => EstadoLegajo::Activo,
            'situacion_estudios' => SituacionEstudios::EnCurso,
        ]);

        foreach ($comision->detalles()->get() as $detComision) {
            AlumnoComision::factory()->create(['alumno_id' => $alumno->id, 'det_comision_id' => $detComision->id]);
        }

        $convocatoria = ConvocatoriaBeca::factory()->create([
            'tipo_beca_id' => TipoBeca::factory()->create(['nombre_beca' => 'Beca de Excelencia', 'valor_descuento' => 10])->id,
        ]);
        $solicitud = SolicitudBeca::factory()->create(['alumno_id' => $alumno->id, 'convocatoria_beca_id' => $convocatoria->id]);
        Beca::factory()->create([
            'alumno_id' => $alumno->id,
            'convocatoria_beca_id' => $convocatoria->id,
            'solicitud_beca_id' => $solicitud->id,
            'valor_beca' => 10,
        ]);

        // Un preinscripto (sin legajo) para CU2.
        $preinscripto = User::factory()->sinContrasenia()->create([
            'email' => 'preinscripto@las.test', 'apellido_usuario' => 'Ramírez', 'nombre_usuario' => 'Ana',
            'dni_usuario' => '40888888', 'localidad_id' => $localidad->id,
        ]);
        $preinscripto->syncRoles([RolSistema::Alumno->value]);
        Alumno::create([
            'user_id' => $preinscripto->id,
            'carrera_id' => $carrera->id,
            'plan_estudio_id' => $plan->id,
            'estado_alumno' => EstadoAlumno::Preinscripto,
            'situacion_estudios' => SituacionEstudios::EnCurso,
        ]);
    }
}
