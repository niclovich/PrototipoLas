# Diferencias entre el prototipo y los diagramas de la tesis

Lo mantiene el Agente Desarrollador. Cada fila es algo que el código hace distinto de un diagrama (o de la especificación) para que Nicolás decida si ajusta la tesis. No se revierte sin su decisión.

Columna **Etapa**: `E0` = ya implementado; `Plan` = acordado en `docs/planes/00_general.md` §4, se implementa en la etapa indicada del plan.

## Implementado en E0 (base común)

| CU | Diagrama | Qué dice | Qué se hizo | Por qué | Etapa |
|---|---|---|---|---|---|
| Base | 4 hojas H1 | Usuario.idUsuario Integer | `users.id` bigint autoincremental | Convención Eloquent; mapeo en `docs/trazabilidad.md` | E0 |
| Base | 4 hojas H1 | Usuario.correoUsuario, contrasenia | `users.email` (único, login), `users.password` nullable + `email_verified_at`, `remember_token` | Fortify; login por correo (skill §1/§2). `password` es nulo hasta que el usuario la define con el link firmado | E0 |
| Base | 4 hojas H1 | Usuario.estadoCuenta String | `users.estado_cuenta` string + Enum `EstadoCuenta` (`Activo`/`Inactivo`) | Skill §3; la baja de usuario es por estado (pestaña "Eliminados" = `Inactivo`) | E0 |
| Base | 4 hojas H1 | Usuario 1..1 Localidad | `users.localidad_id` todavía no existe | La tabla `localidades` se crea en E1; ahí se agrega la FK (plan §3.2/§3.10) | E0 (pendiente E1) |
| Base | 4 hojas H1 | Clase Rol (idRol, nombreRol, descripcionRol, estadoRol) | Tabla `roles` de Spatie (`id`, `name`) + columnas `descripcion`, `estado` (Enum `EstadoGeneral`); modelo `App\Models\Rol` | Skill §2 | E0 |
| Base | 4 hojas H1 | Subclases DirectorGeneral, Coordinador, Preceptor, AuxiliarAdministrativo, Docente, Cajero | Sin tablas: un rol Spatie por usuario (`syncRoles`), Enum `RolSistema` | No tienen atributos propios; la restricción "el Rol del Usuario debe corresponder a su subclase" se cumple porque la subclase es el rol | E0 |
| Base | — (no está en la tesis) | — | Rol técnico `SUPERADMIN` con acceso total por `Gate::before`; permisos de Administración (usuarios, roles, catálogos, auditoría) | Skill §1; no es un actor de la tesis | E0 |
| Base | 4 hojas H1 | Rol con estadoRol | Los 8 roles de la tesis no se pueden renombrar ni dar de baja desde la pantalla de Roles; solo los roles creados a mano | Los permisos por CU dependen de esos nombres | E0 |
| Base | — | — | El módulo Usuarios (SUPERADMIN) no permite asignar el rol Alumno | El usuario alumno se crea en CU1 / CU 9 junto con su Alumno y legajo | E0 |
| CU 25 | Especificación p. 143 | "cuenta de acceso (DNI y Contraseña)" | Login con correo y contraseña; DNI único pero no se usa para entrar | Skill §1/§2 (test `LoginTest::test_el_dni_no_sirve_para_ingresar`) | E0 |
| Base | Skill §1 | authenticateUsing: cuenta activa → si es Alumno, legajo Activo → Hash::check | Se verifica la contraseña antes que el legajo; el mensaje "Este usuario no está activo" sale solo con contraseña correcta | Orden interno flexible (§0): no revela qué correos existen | E0 |
| Base | Skill §1 | simplesoftwareio/simple-qrcode | bacon/bacon-qr-code 3.1.1 directo (`App\Services\Documentos\QrService`, SVG en data URI) | Conflicto de dependencias con Fortify (plan §5.1, §7-2) | E0 |
| Menú | Skill §2 | Un ítem de menú por CU | Un ítem por pantalla de entrada (plan §2.3) | Los diagramas de estados hacen que CU2, CU3, CU5 y CU7 empiecen en la misma P1 "Gestión de Alumnos" | E0 |
| CU 9 | Lista p. 58 | Actor PR | Permiso `crear_usuario_alumno` a PR y AA | CU1 (AA–PR) lo invoca en su escenario alternativo 4.3 | E0 (permiso) |
| CU 33/34 | Especificación | Actor solo DG (colab 34: DG) | Permisos de cuotas a DG y CC | Lista de CU p. 59: CC – DG | E0 (permiso) |

## Implementado en E1 (modelo de datos)

| CU | Diagrama | Qué dice | Qué se hizo | Por qué | Etapa |
|---|---|---|---|---|---|
| Base / CU 25 / CU 9 | 4 hojas H1 | Usuario 1..* — 1..1 Localidad | `users.localidad_id` FK **nullable** | Los usuarios técnicos (SUPERADMIN, altas desde Administración) no cargan domicilio. Personal (CU 25) y Usuarios Alumno (CU 9/CU1) la exigirán en su FormRequest | E1 |
| Base | 4 hojas H1 | Usuario.sexo, estadoCivil String | Enums `Sexo` (Femenino, Masculino, X) y `EstadoCivil` (Soltero/a, Casado/a, Divorciado/a, Viudo/a, Unión convivencial) | Valores confirmados por Nicolás (plan §7-12) | E1 |
| Catálogos | 4 hojas | Provincia, Localidad, Modalidad, Duración, Régimen, Cuatrimestre, Llamado, TipoActa, RolTribunal, TipoDocumento, Motivo, MedioPago, TipoGestionTitulo sin CU | Tablas con `deleted_at` (SoftDeletes) y un ABM genérico para el SUPERADMIN (`config/catalogos.php`, `CatalogoController`) | Plan §3.3 y fila E1 de §6. La baja lógica conserva las referencias | E1 |
| CU6 / Título | 4 hojas H3 | TituloTramite 1..1 TipoGestionTitulo | El catálogo `tipos_gestion_titulo` queda **vacío** | La tesis no define sus valores y Gestionar Trámite de Título está fuera de alcance (pendiente) | E1 |
| Soporte 27 | 4 hojas H1 | Motivo (descripción) | `motivos.accion` + unique(accion, descripcion); valores de "Baja de Personal": Renuncia, Jubilación, Fin de contrato, Otro | Plan §3.3/§5.7 | E1 |
| CU7 / Becas | 4 hojas H4 | TipoBeca.tipoDescuento, SolicitudBeca.estadoSolicitud | Columnas `string` **sin Enum** | La tesis no define sus valores; Becas está fuera de alcance (plan §7-8). Se cargan por seeder | E1 |
| CU7 | 4 hojas H4 | Pago 1..1 MedioPago | `pagos.medio_pago_id` NOT NULL; no existe `pago_medios` | Decisión de Nicolás §7-5 (un solo medio por pago) | E1 |
| CU5 / CU7 | 4 hojas | DetalleActa y FacturaComprobante | Columna `uuid` única, generada por el modelo (`HasUuids` sobre `uuid`, la PK sigue siendo `id`) | QR a la ruta pública de validación | E1 |
| CU7 | 4 hojas H4 | FacturaComprobante.numeroComprobante | Se muestra como `0001-00000025` (`LAS_PUNTO_VENTA` + número) | Plan §7-6 | E1 |

## Acordado en el plan general (§4)

Las filas marcadas `E1` ya están en las migraciones `database/migrations/0001_01_02_*` y en los modelos de `app/Models`. Las marcadas `Plan` se implementan en la etapa del CU.

| CU | Diagrama | Qué dice | Qué se hizo | Por qué | Etapa |
|---|---|---|---|---|---|
| General | 4 hojas y todos | PK `idXxx` Integer | PK `id`, FK `{entidad}_id` | Convención Eloquent; trazabilidad en `docs/trazabilidad.md` | E0 / E1 |
| General | 4 hojas | Estados como `String` | `string` + Enum PHP con los valores de §3 | §1b/§3; evita valores libres | E1 |
| General | Colaboraciones Materia, Comisión, Personal, Apertura de Caja | `"Activa"`, `"Inactiva"`, `estadoCaja='Abierto'` | `Activo`/`Inactivo`; Caja `Abierta`/`Cerrada` | §3 (estado activo siempre masculino; excepción Caja). Ver §7-11 para los textos de los wireframes | E1 |
| CU 9 / CU1 | `usuario_alumno/clases_analisis` | provincia, localidad como String; idiomas String | `localidad_id` FK (provincia por la localidad); `idiomas` string(255) | 4 hojas exige FK a Localidad; wireframe P3 es multiselección de idiomas | E1 |
| CU 9 | 4 hojas H1 | `idiomas: Integer` | `idiomas` string(255) (lista) | El wireframe pide "Idiomas que habla" (Español, Guaraní…) | E1 |
| Soporte 3/4 | `carrera/clases_analisis_modificar` | modalidadCarrera, duracionCarrera como atributos | FK `modalidad_id`, `duracion_id` | 4 hojas y diseño | E1 |
| Soporte 17, 6 | 4 hojas H2 | Duracion solo `duracion: String` | + `cantidad_anios` tinyint | CU 17 valida "Año de Curso entre 1 y la duración de la Carrera"; CU 6 muestra "Años de Duración" | E1 |
| Soporte 7 | Análisis/diseño baja materia del plan | `DetallePlanEstudio.fechaBaja` (no está en 4 hojas) | Se agrega `fecha_baja` null | La colaboración la carga | E1 |
| CU2 | `CU2/clases_analisis`, `clases_diseno` | Alumno.presentaTitulo, tituloSecundario | No se agregan (pendiente §7-17) | No están en 4 hojas; se deriva de DocumentoPresentado | E1 |
| CU1–CU7 | 4 hojas H2 | Alumno "cursa" PlanEstudio | `alumnos.plan_estudio_id` NOT NULL = plan activo al preinscribir | Multiplicidad 1..1 | E1 |
| CU3 | 4 hojas H2 | Alumno "cursa actualmente en" Comision 0..1 | `alumnos.comision_actual_id` null | §3 | E1 |
| CU4/CU6 | 4 hojas H2 | EstadoCurricular sin identificador | Se agrega `id` | Requisito de Eloquent | E1 |
| CU6 | 4 hojas H2 | EstadoCurricular.nroActa Integer | Se mantiene `nro_acta` (sin FK) | Fiel al diagrama | E1 |
| Soporte 8 | 4 hojas H2 | Correlativa con dos asociaciones a DetallePlanEstudio | `detalle_plan_estudio_id` y `detalle_plan_correlativa_id`; `p_cursar`/`p_rendir` nullables | Una fila por correlativa con condición para cursar y/o rendir | E1 |
| Soporte 8 | Especificación p. 121 | "Registrar correlatividades … en estado Activo" | No se agrega estado | Correlativa no tiene atributo de estado (§7-20) | E1 |
| CU7 | Wireframe P2 | Combo medio + monto + "Agregar" sugiere varios medios | Se respeta la tesis: Pago 1..1 MedioPago. "Agregar" fija el único medio del pago (reemplaza el anterior); "Importe en <medio>" muestra lo agregado; "Saldo Pendiente" = Total a Pagar − importe; "Registrar Pago" se habilita con saldo 0 | Decisión de Nicolás §7-5 (2026-10-25): un solo medio de pago | Plan |
| CU7 / CU5 | 4 hojas | FacturaComprobante y DetalleActa sin identificador público | + `uuid` | QR a ruta pública de validación (§1) | E1 |
| CU 35/36 | 4 hojas H4 | fechaApertura/fechaCierre `Date` | `dateTime` | El wireframe de cierre muestra hora (15/12/2024 18:38:34) | E1 |
| CU 36 | 4 hojas H4 | Caja.montoFinal | Se completa al cerrar = suma de montos contados | La colaboración de cierre no lo carga | Plan |
| CU6 | 4 hojas H2 | TituloTramite.nroLegajo; TituloTramite 1..1 TipoGestionTitulo | Sin `nro_legajo` (se obtiene del alumno); `tipo_gestion_titulo_id` null | Redundante; CU6 no informa el tipo de gestión | E1 |
| CU6 | Análisis CU6 | ActaExamen 1..* DetallePlanEstudio/Llamado/TipoActa | Se usa 0..* (4 hojas) | 4 hojas es la versión corregida | E1 |
| Soporte 21 | 4 hojas H3 | Tribunal sin atributos, 1..1 con Acta | Se mantiene tabla `tribunales` | Fiel al diagrama (alternativa: FK directa acta→integrantes) | E1 |
| Soporte 21 | Análisis acta | ActaExamen 1..1 — 1..* Tribunal | 1..1 (4 hojas) | Versión corregida | E1 |
| CU1–CU5 | 4 hojas H1/H2 | Novedad asociada a Usuario y a Alumno | `novedades.user_id` NOT NULL, `alumno_id` null | Dos asociaciones; la de Alumno es de CU fuera de alcance | E1 |
| Equivalencias/Becas | 4 hojas H3/H4 | Clases de equivalencias y becas | Equivalencias no se crean; Becas se crean sin pantallas | Fuera de alcance; CU7 necesita Beca activa | E1 |
| Base | — | Bajas de entidades principales | SoftDeletes solo en catálogos; bajas de la tesis por estado | La especificación define estados de baja explícitos | E1 |

## Desvíos respecto del plan (no son diferencias con un diagrama)

| Tema | Plan | Qué se hizo | Por qué |
|---|---|---|---|
| Rate limit del login | §5.4: `RateLimiter::for('login', 5/min por correo+IP)` | `config/fortify.php` `'limiters' => ['login' => null]`: se usa el `LoginRateLimiter` de Fortify (misma clave `lower(email)|ip`, 5 intentos por minuto) | Devuelve el error en el formulario en vez de una página 429. Test `LoginTest::test_el_limite_es_por_correo_e_ip` |
| Fechas en formularios | §1b: formato dd/mm/aaaa | `<x-form.date>` envía dd/mm/aaaa; cada FormRequest valida `date_format:d/m/Y` y el Service convierte a `Y-m-d` | Formato argentino visible y enviado igual |
| Tests | §5.8: BD `sistema_las_test` | Además `APP_URL=http://localhost` en `phpunit.xml` | Las URL (y las firmadas) no dependen de la carpeta de XAMPP |
| Menú | §2.3 | Los módulos sin implementar apuntan a rutas provisorias con su nombre y permiso definitivos (`ModuloPendienteController`) | Cada etapa reemplaza su línea en `routes/panel.php` |
| Usuarios | §5 | Los usuarios Alumno no aparecen ni se editan en Administración → Usuarios; solo un SUPERADMIN asigna o gestiona SUPERADMIN; nadie cambia su propio rol | Se gestionan desde CU1/CU 9; evita escalar privilegios (revisión E0) |
| Confirmaciones | §1b | Todas las bajas/reactivaciones con SweetAlert2 (`data-confirm`); `<x-modal-confirmar>` queda disponible para modales con contenido | Un solo estilo (revisión E0) |
| Auditoría | §3.1 | `AuditaCambios` registra todos los atributos que cambian (salvo contraseña, token y timestamps), no solo `$fillable` | `estado_cuenta`, `password`, `fecha_alta` se asignan con `forceFill` |
| Migraciones | §3.10: una migración por tabla | Una migración por grupo de §3.10 (catálogos, académico, alumnos, exámenes, finanzas) respetando el orden | Menos archivos; cada `down()` borra en orden inverso. `migrate:rollback` probado |
| Demo | §5.7 | `DemoSeeder` solo con `APP_ENV=local`; contraseña de los usuarios demo `Demo1234` | Datos de prueba para las etapas siguientes |
