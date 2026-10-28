<?php

namespace Tests\Feature\Modelos;

use App\Enums\EstadoCaja;
use App\Enums\EstadoLegajo;
use App\Enums\EstadoTramite;
use App\Enums\RolSistema;
use App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ConRolesYPermisos;
use Tests\TestCase;

/**
 * Cada modelo se crea con su factory y cada relación devuelve el tipo esperado; las BelongsTo
 * obligatorias (FK NOT NULL, multiplicidad 1..1) resuelven el registro relacionado.
 */
class RelacionesModelosTest extends TestCase
{
    use ConRolesYPermisos;

    /** @return array<string, array{class-string<Model>, string, array<string, class-string>}> */
    public static function modelos(): array
    {
        $b = BelongsTo::class;
        $m = HasMany::class;
        $o = HasOne::class;

        return [
            'Provincia' => [Models\Provincia::class, 'provincias', ['localidades' => $m]],
            'Localidad' => [Models\Localidad::class, 'localidades', ['provincia' => $b, 'usuarios' => $m]],
            'Modalidad' => [Models\Modalidad::class, 'modalidades', ['carreras' => $m, 'comisiones' => $m, 'actas' => $m]],
            'Duracion' => [Models\Duracion::class, 'duraciones', ['carreras' => $m]],
            'Regimen' => [Models\Regimen::class, 'regimenes', ['detallesPlan' => $m]],
            'Cuatrimestre' => [Models\Cuatrimestre::class, 'cuatrimestres', ['detallesPlan' => $m]],
            'Llamado' => [Models\Llamado::class, 'llamados', ['actas' => $m]],
            'TipoActa' => [Models\TipoActa::class, 'tipos_acta', ['actas' => $m]],
            'RolTribunal' => [Models\RolTribunal::class, 'roles_tribunal', ['integrantes' => $m]],
            'TipoDocumento' => [Models\TipoDocumento::class, 'tipos_documento', ['documentos' => $m]],
            'Motivo' => [Models\Motivo::class, 'motivos', ['novedades' => $m]],
            'MedioPago' => [Models\MedioPago::class, 'medios_pago', ['pagos' => $m, 'detallesCierre' => $m]],
            'TipoGestionTitulo' => [Models\TipoGestionTitulo::class, 'tipos_gestion_titulo', ['titulos' => $m]],
            'User' => [Models\User::class, 'users', ['localidad' => $b, 'encuesta' => $o, 'alumnos' => $m, 'documentos' => $m, 'novedades' => $m, 'cajas' => $m, 'integracionesTribunal' => $m]],
            'EncuestaSocioeconomica' => [Models\EncuestaSocioeconomica::class, 'encuestas_socioeconomicas', ['usuario' => $b]],
            'Carrera' => [Models\Carrera::class, 'carreras', ['modalidad' => $b, 'duracion' => $b, 'planesEstudio' => $m, 'planActivo' => $o, 'comisiones' => $m, 'alumnos' => $m, 'cuotas' => $m]],
            'Materia' => [Models\Materia::class, 'materias', ['detallesPlan' => $m]],
            'PlanEstudio' => [Models\PlanEstudio::class, 'planes_estudio', ['carrera' => $b, 'detalles' => $m, 'alumnos' => $m]],
            'DetallePlanEstudio' => [Models\DetallePlanEstudio::class, 'detalles_plan_estudio', ['planEstudio' => $b, 'materia' => $b, 'regimen' => $b, 'cuatrimestre' => $b, 'correlativas' => $m, 'esCorrelativaDe' => $m, 'detComisiones' => $m, 'actas' => $m, 'estadosCurriculares' => $m]],
            'Correlativa' => [Models\Correlativa::class, 'correlativas', ['detallePlan' => $b, 'detallePlanCorrelativa' => $b]],
            'Comision' => [Models\Comision::class, 'comisiones', ['carrera' => $b, 'modalidad' => $b, 'detalles' => $m, 'alumnosActuales' => $m]],
            'DetComision' => [Models\DetComision::class, 'det_comisiones', ['comision' => $b, 'detallePlan' => $b, 'inscripciones' => $m]],
            'Alumno' => [Models\Alumno::class, 'alumnos', ['usuario' => $b, 'carrera' => $b, 'planEstudio' => $b, 'comisionActual' => $b, 'inscripcionesComision' => $m, 'estadosCurriculares' => $m, 'detallesActa' => $m, 'tituloTramite' => $o, 'novedades' => $m, 'pagos' => $m, 'solicitudesBeca' => $m, 'becas' => $m]],
            'DocumentoPresentado' => [Models\DocumentoPresentado::class, 'documentos_presentados', ['usuario' => $b, 'tipoDocumento' => $b]],
            'Novedad' => [Models\Novedad::class, 'novedades', ['usuario' => $b, 'alumno' => $b, 'motivo' => $b]],
            'AlumnoComision' => [Models\AlumnoComision::class, 'alumno_comision', ['alumno' => $b, 'detComision' => $b]],
            'EstadoCurricular' => [Models\EstadoCurricular::class, 'estados_curriculares', ['alumno' => $b, 'detallePlan' => $b]],
            'Tribunal' => [Models\Tribunal::class, 'tribunales', ['acta' => $o, 'integrantes' => $m]],
            'ActaExamen' => [Models\ActaExamen::class, 'actas_examen', ['detallePlan' => $b, 'modalidad' => $b, 'llamado' => $b, 'tipoActa' => $b, 'tribunal' => $b, 'detalles' => $m]],
            'IntegranteTribunal' => [Models\IntegranteTribunal::class, 'integrantes_tribunal', ['tribunal' => $b, 'docente' => $b, 'rolTribunal' => $b]],
            'DetalleActa' => [Models\DetalleActa::class, 'detalles_acta', ['acta' => $b, 'alumno' => $b]],
            'TituloTramite' => [Models\TituloTramite::class, 'titulos_tramite', ['alumno' => $b, 'tipoGestion' => $b]],
            'Cuota' => [Models\Cuota::class, 'cuotas', ['carrera' => $b, 'mes' => $b, 'detallesPago' => $m]],
            'TipoBeca' => [Models\TipoBeca::class, 'tipos_beca', ['convocatorias' => $m]],
            'ConvocatoriaBeca' => [Models\ConvocatoriaBeca::class, 'convocatorias_beca', ['tipoBeca' => $b, 'solicitudes' => $m, 'becas' => $m]],
            'SolicitudBeca' => [Models\SolicitudBeca::class, 'solicitudes_beca', ['alumno' => $b, 'convocatoria' => $b, 'beca' => $o]],
            'Beca' => [Models\Beca::class, 'becas', ['alumno' => $b, 'convocatoria' => $b, 'solicitud' => $b, 'detallesPago' => $m]],
            'Caja' => [Models\Caja::class, 'cajas', ['cajero' => $b, 'pagos' => $m, 'detallesCierre' => $m]],
            'DetalleCierreCaja' => [Models\DetalleCierreCaja::class, 'detalles_cierre_caja', ['caja' => $b, 'medioPago' => $b]],
            'Pago' => [Models\Pago::class, 'pagos', ['alumno' => $b, 'caja' => $b, 'medioPago' => $b, 'detalles' => $m, 'factura' => $o]],
            'DetallePagoCuota' => [Models\DetallePagoCuota::class, 'detalles_pago_cuota', ['pago' => $b, 'cuota' => $b, 'beca' => $b]],
            'FacturaComprobante' => [Models\FacturaComprobante::class, 'facturas_comprobante', ['pago' => $b]],
        ];
    }

    /** @param  array<string, class-string>  $relaciones */
    #[DataProvider('modelos')]
    public function test_factory_tabla_y_relaciones(string $clase, string $tabla, array $relaciones): void
    {
        $modelo = $clase::factory()->create();

        $this->assertSame($tabla, $modelo->getTable());
        $this->assertDatabaseHas($tabla, ['id' => $modelo->getKey()]);

        foreach ($relaciones as $nombre => $tipo) {
            $relacion = $modelo->{$nombre}();
            $this->assertInstanceOf($tipo, $relacion, "{$clase}::{$nombre}()");

            if ($relacion instanceof BelongsTo && $modelo->{$relacion->getForeignKeyName()} !== null) {
                $this->assertNotNull($modelo->{$nombre}()->first(), "{$clase}::{$nombre} no resuelve el registro relacionado.");
            }
        }
    }

    public function test_solo_los_catalogos_usan_soft_deletes(): void
    {
        $catalogos = ['Provincia', 'Localidad', 'Modalidad', 'Duracion', 'Regimen', 'Cuatrimestre', 'Llamado', 'TipoActa', 'RolTribunal', 'TipoDocumento', 'Motivo', 'MedioPago', 'TipoGestionTitulo'];

        foreach (self::modelos() as $nombre => [$clase]) {
            $this->assertSame(in_array($nombre, $catalogos, true), in_array(SoftDeletes::class, class_uses_recursive($clase), true), $nombre);
        }
    }

    public function test_relaciones_inversas_y_casts(): void
    {
        $usuario = $this->crearUsuario(RolSistema::Alumno);
        $alumno = Models\Alumno::factory()->for($usuario, 'usuario')->create(['estado_legajo' => EstadoLegajo::Activo, 'nro_legajo' => 10000]);
        Models\TituloTramite::factory()->for($alumno)->create();
        $caja = Models\Caja::factory()->create();
        $pago = Models\Pago::factory()->create(['alumno_id' => $alumno->id, 'caja_id' => $caja->id]);
        $factura = Models\FacturaComprobante::factory()->create(['pago_id' => $pago->id, 'numero_comprobante' => 25]);

        $alumno->load(['usuario', 'tituloTramite', 'pagos.factura', 'pagos.medioPago']);

        $this->assertTrue($alumno->usuario->is($usuario));
        $this->assertSame(EstadoLegajo::Activo, $alumno->estado_legajo);
        $this->assertSame(EstadoTramite::SinGestion, $alumno->tituloTramite->estado_tramite);
        $this->assertSame(EstadoCaja::Abierta, $caja->fresh()->estado_caja);
        $this->assertTrue($alumno->pagos->first()->factura->is($factura));
        $this->assertNotNull($alumno->pagos->first()->medioPago, 'Un Pago tiene un solo MedioPago (decisión §7-5).');
        $this->assertSame('0001-00000025', $factura->numeroFormateado());
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $factura->uuid);
        $this->assertSame(1, $usuario->alumnos()->conLegajoActivo()->count());
    }

    /** Mes no tiene factory (sus valores son fijos, id = orden): se prueba con los precargados. */
    public function test_mes_precargado_y_su_relacion_con_cuotas(): void
    {
        $marzo = Models\Mes::precargados()->firstWhere('descripcion', 'Marzo');
        $cuota = Models\Cuota::factory()->create(['mes_id' => $marzo->id]);

        $this->assertSame('meses', $marzo->getTable());
        $this->assertInstanceOf(HasMany::class, $marzo->cuotas());
        $this->assertTrue($marzo->cuotas()->whereKey($cuota->id)->exists());
        $this->assertTrue($cuota->mes->is($marzo));
    }

    public function test_scopes_de_subclases_de_usuario(): void
    {
        $docente = $this->crearUsuario(RolSistema::Docente);
        $cajero = $this->crearUsuario(RolSistema::Cajero);

        $this->assertSame([$docente->id], Models\User::docentes()->pluck('id')->all());
        $this->assertSame([$cajero->id], Models\User::cajeros()->pluck('id')->all());
    }
}
