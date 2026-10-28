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
| Base | — (no está en la tesis) | — | Rol técnico `SUPERADMIN` con todos los permisos asignados explícitamente y respaldo de acceso total por `Gate::before` | Skill §1; no es un actor de la tesis y debe acceder a todas las pantallas | E0 |
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
| CU7 | 4 hojas H4 | Pago.fechaPago `Date` | `pagos.fecha_pago` `dateTime` | El comprobante de pago y el cierre de caja muestran la hora (igual que fechaApertura/fechaCierre de Caja) | E1 |
| CU7 | 4 hojas H4 | Beca **1..1** "bonifica" 0..* DetallePagoCuota | `detalles_pago_cuota.beca_id` **nullable** (0..1) | No todo alumno tiene beca: el detalle sin bonificación no tiene Beca. Proponer corregir la multiplicidad del diagrama a 0..1 | E1 |
| CU1 / Becas | 4 hojas H1/H3/H4 | DocumentoPresentado "contiene" 1..1 SolicitudBeca / SolicitudEquivalencia | `documentos_presentados` sin `solicitud_beca_id` ni FK a equivalencias | Fuera de alcance: no hay pantallas de becas ni equivalencias (las tablas de equivalencias no se crean) | E1 |
| Catálogos | — | Motivo.accion texto | `accion` se elige de una lista fija (`Motivo::ACCIONES`, hoy "Baja de Personal") | CU 27 filtra por esa acción; un texto libre mal escrito haría desaparecer el motivo | E1 |
| Catálogos | 4 hojas H1 | Localidad 1..1 Provincia | No se puede dar de baja una Provincia con localidades activas | Evita localidades con provincia eliminada (no se podrían editar) | E1 |

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

## Implementado en E2 (Personal: CU 25, 26, 27)

| CU | Diagrama | Qué dice | Qué se hizo | Por qué | Etapa |
|---|---|---|---|---|---|
| 25 | Colaboración (DD `dp`, `du`) | `estadoCuenta="Activa"`; `fechaAlta` viaja en `dp` | `estado_cuenta = Activo`; `fecha_alta = today()` la fija el servidor | §3; la spec dice "con Fecha de Alta" del día | E2 |
| 25 | Colaboración / diseño | `contrasenia` String | `password` con hash bcrypt (cast `hashed`) | Seguridad | E2 |
| 25 | Diseño registrar (Fig. 159) vs análisis (Fig. 107) | Diseño: Docente, Cajero, Coordinador, **Administrativo**, DirectorGeneral (sin Preceptor) | 6 roles del personal (`RolSistema::delPersonal()`), sin tablas por subclase | Postcondición del CU 25 y Hoja 1; corregir el diseño (falta Preceptor y el rol es "Auxiliar Administrativo") | E2 |
| 25/26/27 | Spec 16–17 / 15–16 / 11–12 | Muestra los datos y solicita confirmación | El formulario/modal con los datos a la vista es la confirmación; su botón es "Confirma" | Los diagramas de estados van de P2 a la Confirmación sin pantalla intermedia (D8) | E2 |
| 25 | — | — | Al registrar se envía la verificación de correo (Fortify) | El panel exige correo verificado y el acceso es por correo (D12) | E2 |
| 25 | Wireframe P2 y spec | El DG carga la contraseña; cuenta "DNI y Contraseña" | Se carga la contraseña (no hay link firmado); se entra con correo | Wireframe rígido + login por correo (§7-4, D1) | E2 |
| 26 | Colaboración p. 196 | "el Rol … debe corresponder a la subclase con la que fue registrado" | Se puede cambiar el Rol (`syncRoles`) | Spec pasos 12–13 y 18 y el wireframe lo permiten (D13) | E2 |
| 26 | — | — | Si cambia el correo, `email_verified_at = null` y se envía la verificación | Igual que Usuarios (E0) | E2 |
| 26/27 | — | — | Un DG no puede cambiar su propio rol ni darse de baja | Evita quedar sin acceso (D11) | E2 |
| 27 | Colaboración | `actualizar(estadoCuenta="Inactiva")` | `Inactivo` + invalida "Recordarme" y borra sesiones | §3; postcondición "no puede volver a ingresar" | E2 |
| 27 | Colaboración `dbp.fechaNovedad` | La fecha viaja en los datos | `fecha_novedad = today()` en el servidor; el modal la muestra en solo lectura | El campo "Fecha" del wireframe no es editable | E2 |
| 27 | Spec 9–10 | Ingresa la Observación | Motivo obligatorio, Observación opcional (≤ 1000) | `observacion_novedad` es nullable; sin escenario si falta (D17) | E2 |
| 25/26 | Análisis / diseño | Provincia no figura en el diseño (solo Localidad) | La Provincia filtra las Localidades; se guarda `localidad_id` | Usuario 1..* — 1..1 Localidad | E2 |
| P1 | Wireframe Eliminados | FECHA BAJA, MOTIVO DE BAJA | Se obtienen de la última Novedad con Motivo "Baja de Personal" (`User::ultimaBaja`) | No hay atributos de baja en Usuario | E2 |
| P1/P2/modales | Wireframes | "Activa"/"Inactiva" (Eliminados, Baja) | Se muestra `Activo`/`Inactivo` | §3 y §7-11 (D2). Corregir los PNG | E2 |
| P1/P2/modales | Wireframes | Textos sin tilde: "GESTION", "Telefono", "Direccion", "Descripcion del Rol", "Contraseña invalida", "Datos invalidos", "Observacion" | Se muestran con tilde | Corrección ortográfica (D3) | E2 |
| P1/P2 | Wireframes | Notas grises al pie ("La pestaña Eliminados lista…", "La cuenta de acceso (DNI y Contraseña)…") | No se muestran: son anotaciones del diseñador; sí se muestra la ayuda de Contraseña | Describen comportamiento y contradicen el login por correo (D4) | E2 |
| P1 Eliminados | Wireframe | Ícono ojo (👁) | Deshabilitado con tooltip "Fuera del alcance del prototipo" | Ningún CU lo define (D5) | E2 |
| P1 | Wireframe | Rol "Administrativo" | Se muestra el nombre del rol: "Auxiliar Administrativo" | Lista de actores y análisis (D16) | E2 |
| P1 | Wireframe | Sin controles de DataTables | Se ocultan "Buscar:" y "Mostrar N"; quedan la info y la paginación | El filtro es el del wireframe; la paginación es necesaria con server-side (D14) | E2 |
| Confirmaciones | Estados de los 3 CU | P5 (Registrar) / P3 (Modificar, Baja) Confirmación sin PNG ni texto | `<x-pantalla-confirmacion>`: "El Personal se registró correctamente.", "Los datos del Personal se modificaron correctamente.", "La baja del Personal se registró correctamente." | D6; agregar los textos a la spec y los PNG al Anexo C | E2 |
| 26 | Estados vs PNG | Estados: P3 = Confirmación; PNG `Modificar_P3` = Datos inválidos | Se implementan ambas: modal "Datos inválidos" y pantalla de Confirmación | Renombrar en la tesis (D7) | E2 |
| 25 | Spec | Numeración salta del paso 19 al 21 | Se usa la numeración publicada | Corregir en la tesis (D18) | E2 |
| Contraseña | Spec 15.1 / E0 | Ayuda "Mínimo 8 caracteres…" usada como mensaje en E0 | Mensaje de error = texto de la spec 15.1 en todo el sistema (también restablecer y definir contraseña); el texto corto queda como ayuda | D15 | E2 |

## Implementado en E3 (Usuarios Alumno: CU 9)

| CU | Diagrama | Qué dice | Qué se hizo | Por qué | Etapa |
|---|---|---|---|---|---|
| 9 | Spec p. 122 | Título "CU: Registrar Inscripción del Alumno"; pasos 9, 10, 27, 28 | Se implementa como "Registrar Datos de Usuarios Alumno"; se usa la numeración publicada | Error de copia (§7-21); corregir el título y numerar 9–12 | E3 |
| 9 | — | Sin colaboración, diseño, estados ni wireframes propios | `UsuarioAlumnoService` + FormRequests de P2/P3; la UI es P2/P3 de CU1 (E12) | §7-21 | E3 |
| 9 | Análisis "solo atributos" (Fig. 97) | `apellido`, `nombre`, `telefono`, `email`, `pais`, `provincia`, `localidad`, `foto`… | Columnas de la Hoja 1 (`apellido_usuario`, …, `email`, `localidad_id`, `foto_usuario`) | Unificar los nombres de la Fig. 97 con la Hoja 1 | E3 |
| 9 | Spec paso 2 / Fig. 97 | Solicita País | P2 no tiene País (wireframe rígido); se guarda `pais_usuario` = `LAS_PAIS_POR_DEFECTO` ("Argentina") | Provincias y Localidades son de Argentina (D4) | E3 |
| 9 | Spec paso 9 | "Registrar Usuario en estado Activo" | Activo, fecha de alta hoy, rol Alumno, **sin contraseña** y sin correo. Puede entrar recién cuando tenga legajo Activo y defina la contraseña con el link de CU2 | §1 y §7-18 | E3 |
| 9 | Fig. 97 | `nivelEstudiosMadre/Padre: String` | Enum `NivelEstudios` (Sin estudios formales … Universitario completo) | Lista cerrada para el select del PNG (D3, editable) | E3 |
| 9 | Fig. 97 | `idiomas: String` | Lista del Enum `Idioma` guardada como "Español, Guaraní" | Multiselección del PNG (D3, editable) | E3 |
| 9 | Fig. 97 | `foto: String` | Ruta del archivo en el disco `public` (`fotos-usuarios/`), jpg/jpeg/png ≤ 2 MB | Se sube un archivo (D5) | E3 |
| 9 / CU1 | PNG P2 | Solo Apellido* y Nombre* | También son obligatorios Correo, Provincia y Localidad (y el DNI de P1) | Login y avisos por correo; Usuario 1..1 Localidad (D1) | E3 |
| 9 / CU1 | Spec paso 2 vs PNG P2 | Orden Teléfono, Celular, Email | En P2 el orden es Correo, Teléfono, Celular (manda el wireframe) | Wireframe rígido; ajustar el orden de la spec | E3 |
| 9 / CU1 | PNG P2 | Sexo y Estado civil como cajas | Selects con los valores de los Enums | Misma posición y label (D9) | E3 (contrato; la vista es de E12) |
| 9 | Spec, actor | "Preceptor / Coordinador de Carrera" | Permiso `crear_usuario_alumno` a Preceptor y Auxiliar Administrativo, no al Coordinador | El Coordinador no es actor de CU1, que es quien lo invoca (D10) | E3 |
| 9 / CU1 | Spec 6–8 vs estados de CU1 | Spec: confirmar antes de registrar. Estados: "P3 Continuar / A: guarda" | Pendiente de D7 (E12). El servicio permite persistir todo en P5 | D7 | E12 |

## Implementado en E4 (Carrera: CU 3, 4)

| CU | Diagrama | Qué dice | Qué se hizo | Por qué | Etapa |
|---|---|---|---|---|---|
| 3/4 | Spec 14–15 / 7 | Solicita y confirma la registración/modificación | El formulario P2 y su botón ("Registrar Carrera" / "Guardar Cambios") son la confirmación | Los estados van P2 → P3 sin pantalla intermedia (igual que D8 de E2) | E4 |
| 3 | Colaboración (DD `dc`, `d`) | `estadoCarrera="Pendiente"` viaja en los datos | El servidor fija `Pendiente`; el request no lo trae | Evita que el cliente lo altere | E4 |
| 4 | Colaboración Fig. 130 | Siempre `actualizar(d)` y re-`vincular()` Modalidad y Duración | Con Plan de Estudio activo solo se actualiza el Estado y no se re-vincula nada | Spec paso 5/10 y Aclaración p. 113 | E4 |
| 4 | Colaboración / diseño | Sin mensaje para "verificar Plan de Estudio Activo" (paso 4) | `CarreraService::poseePlanActivo()` (consulta `PlanEstudio`) | Falta en el diagrama (D9); agregar p. ej. `existePlanActivo()` | E4 |
| 4 | Diseño Fig. 131 | `Modalidad.ubicar()` / `Duracion.ubicar()`; `vincular()` | `associate()` de Eloquent | Implementación | E4 |
| 3/4 | Spec 13 / 9 | "formato correcto" sin definir | Regla `ResolucionMinisterial`: `1234/2020` con prefijo opcional "Resolución N°"; se guarda `1234/2020` y se muestra "Resolución N° 1234/2020" | D1 de E4 (el ejemplo del wireframe tiene prefijo) | E4 |
| 4 | Spec 9 | Valida el formato "si se modificaron nombre o resolución" | El formato se valida solo si la resolución cambió | Evita bloquear carreras cargadas antes con otro formato | E4 |
| P1 | Wireframe | ESTADO como texto "ACTIVA"/"PENDIENTE"; select "Activa" | Badge y opciones `Activo`/`Pendiente`/`Inactivo` | §3, §7-11 (D2) | E4 |
| P1 / P2 | Wireframes | "GESTION", "DURACION", "RESOLUCION", "ACCION", "Duracion", "anios" | Con tilde y "años" | Ortografía (D3) | E4 |
| P1 | Wireframe | Íconos 👁 y 🗑 | Siempre visibles, deshabilitados, con tooltip "Fuera del alcance del prototipo" | Ningún CU define ver ni dar de baja una Carrera (D4) | E4 |
| 4 | Spec / Aclaración p. 113 | Estado modificable, sin lista ni restricciones | Pendiente, Activo e Inactivo, sin restricciones de transición | D5; si "Activo" debe exigir plan, agregar el escenario a la spec | E4 |
| 3/4 | p. 212 + `Registrar_P3` | Confirmación de Modificar sin PNG ni texto | Misma tarjeta que `Registrar_P3`, con título "Carrera modificada exitosamente" | D6; agregar el texto a la spec | E4 |
| 4 | PNG Modificar | Sin variante "solo Estado" | Mismos campos, deshabilitados, con tooltip "La carrera posee un Plan de Estudio activo" | D7 | E4 |
| 3/4 | — | Sin títulos para los modales de error | `<x-modal-aviso>` "Carrera ya existente" / "Resolución inválida" con el mensaje exacto de la spec | D10 | E4 |
| 3/4 | Spec | Obligatorios sin escenario | Todos obligatorios; error debajo del campo, sin modal ("Debe ingresar el nombre de la carrera.", etc.) | D11 | E4 |
| Catálogo | §7-12 | Duración 2, 3 y 4 años | Se agregó "5 años" | El wireframe lo muestra (D12) | E4 |
| 4 | Spec / Fig. 199 | Dos pasos "5"; Fig. 199 se llama "P1" y es P2 | Se usa la numeración publicada | Corregir en la tesis (D13) | E4 |

## Decisiones resueltas en E5 (Materia)

| CU | Fuentes en conflicto | Implementación actual | Decisión requerida | Estado |
|---|---|---|---|---|
| 1 | Wireframe/texto P2 solicita Estado; spec pasos 2/7 registra siempre `Activo` | Select Estado visible con `Activo` y deshabilitado; servidor fuerza `Activo` | **Resuelto por Nicolás, 2026-10-26:** registrar siempre `Activo` y no permitir editar el select | E5-D1 resuelta |
| 2 | Spec paso 4 valida vínculo al seleccionar ✎; texto del wireframe lo ubica en “Guardar Cambios” | P2 se abre y el control ocurre al guardar; si está vinculada vuelve a P1 con el mensaje y no modifica | **Resuelto por Nicolás, 2026-10-26:** prevalece el comportamiento indicado por el texto del wireframe | E5-D2 resuelta |

## Decisiones aprobadas para E6 (Plan de Estudio)

| CU | Fuente / diferencia | Decisión de Nicolás (2026-10-26) | Impacto |
|---|---|---|---|
| 5 | La spec pide un listado detallado; el wireframe muestra un combo | Usar el combo con el nombre de la carrera | Ajustar el paso 2 de la tesis; E6-D1 resuelta |
| 5–8 | CU 5 menciona `Pendiente`; CU 6/8 requieren `Activo` o `Pendiente` | El combo muestra carreras `Activo` y `Pendiente`, nunca `Inactivo` | Una P1 compartida; E6-D2 resuelta |
| 5 | La precondición textual exige `Pendiente`, pero E4 permite una carrera `Activo` sin plan | Permitir registrar un plan para una carrera `Pendiente` o `Activo` sin plan activo | `puedeRegistrarPlan()` acepta ambos estados; E6-D3 resuelta |

## E6–E8 Plan de Estudio, Materia al Plan, Baja y Correlatividades (CU 5–8)

| CU | Diagrama | Qué dice | Qué se hizo | Por qué |
|---|---|---|---|---|
| 5 | Spec pasos 8–10 / 6 24–25 / 8 20–21 | Mostrar datos y pedir confirmación | El formulario P2 y su botón son la confirmación | Estados P2 → P3 (E2 D8) |
| 5 | Colaboración Fig. 132 | `estadoPlanEstudio="Activo"` viaja en los datos | Lo fija el servidor | El cliente no lo altera |
| 5 | Colaboración / diseño | No verifica que la carrera no tenga ya un plan activo | `PlanEstudioService::puedeRegistrarPlan()` con bloqueo | §7-28 (un plan activo por carrera) |
| 5–8 | Wireframe P1 | Cuatrimestre "1C"/"2C"; catálogo "1er Cuatrimestre" | Accessor `Cuatrimestre::abreviatura` | Texto del PNG sin cambiar el catálogo (E6-D6) |
| 6 | Colaboración Fig. 134 / diseño Fig. 135 | Sin `idCuatrimestre` ni clase Cuatrimestre; Fig. 135 titulada "Registrar Plan de Estudio" | Se guarda y vincula `cuatrimestre_id` | Spec 15–17, wireframe y 4 hojas. Corregir el título de Fig. 135 y de Fig. 207 |
| 6 | Spec paso 8 | Valida la materia antes de pedir el código | Se valida al enviar, en el orden de la spec (modal del primer error) | Estados: P2 → P3 al click |
| 6/7 | Estados Figs. 205/208 y texto p. 218 | Botones "Cargar Materia" / "Agregar Materia" | "Registrar Materia" (texto del PNG) | Wireframe (E7-D9) |
| 7 | Colaboración Fig. 136 / diseño | No consulta comisiones (paso 6) | `DetallePlanEstudioService::tieneComisionesDictandose()` (DetComision y Comisión "Activo"); se controla al tocar 🗑 y al confirmar | Falta el mensaje en el diagrama (E7-D3) |
| 7 | Spec paso 10 / estados P3 | Mensaje de baja exitosa sin texto ni PNG | "Materia dada de baja exitosamente" + Carrera, Materia · Código, Fecha de Baja | Modelo de `Registrar_Materia_P3` (E7-D7) |
| 8 | Spec pasos 9–19 | Dos rondas separadas: correlativas para cursar y para rendir | Una fila por correlativa con condición para cursar **y** para rendir; "+ Agregar" y 🗑 en la tabla | Wireframe P2 y colaboración (`pCursar + pRendir`) |
| 8 | Spec paso 23 | "Registrar correlatividades … en estado Activo" | Sin estado (§7-20); la tabla reemplaza las correlativas vigentes (las quitadas con 🗑 se borran) | Correlativa no tiene atributo de estado |
| 8 | Spec | Sin escenarios alternativos | Validaciones propias: materia del mismo plan y de año anterior, sin repetir, condiciones R/A | Integridad de datos (E8-D2) |

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
| Demo | §5.7 | `DemoSeeder` ejecutado por el seeder principal; contraseña de los usuarios demo `Demo1234` | Datos de prueba para las etapas siguientes |
| Estado de cuenta | E0: middleware en el grupo del panel | `AsegurarCuentaActiva` se agrega al grupo `web`: cubre el panel y las rutas de Fortify (`email/verify*`, `user/confirm-password`) | Revisión E1: una cuenta Inactivo con sesión abierta no puede pedir correos ni confirmar contraseña |
| Mes | Factories | No hay `MesFactory`: `Mes::precargados()` crea o completa los 11 valores con id = orden; `CuotaFactory` y `CatalogosSeeder` lo usan | El supuesto "id = orden" de `DeudaCuotasService` se mantiene con o sin seeder |
| Deuda | §7-9 | `DeudaCuotasService::filtrarVencidas` es la única regla de "vencida"; `estaVencida` y `cuotasVencidas` la usan; en listados resuelve las cancelaciones en una consulta | Revisión E1: coherencia entre pantallas y sin N+1 |

## E9 Comisión (CU 17)

| CU | Diagrama / fuente | Qué dice | Qué se hizo | Por qué |
|---|---|---|---|---|
| 17 | Spec paso 22 vs wireframe P2 | Mostrar listado de Materias del año (Nombre, Código, Carga Horaria) y la carga total | Solo "Carga Horaria (calculada)" (wireframe); las materias se vinculan solas | **Decisión pendiente E9-D1**: contradicción spec/wireframe. Recomendación: ajustar el paso 22 |
| 17 | Spec pasos 23–25 | Mostrar datos y pedir confirmación | El P2 y su botón son la confirmación | Estados P2 → P3 (como E6–E8) |
| 17 | Wireframe P1 | Estado "Activa" | Badge `Activo` | §7-11 |
| 17 | Spec / colaboración | Sin control si el plan activo no tiene materias en el año | Error bajo "Año de Curso" (E9-D3), no se registra | Una comisión sin DetComision no sirve a CU3 |
| 17 | Colaboración Fig. 150 | `carga horaria` la calcula Comision | La calcula `ComisionService` (no viaja del cliente) | Mismo resultado, lógica en el Service |
| 17 | P3 | Sin wireframe | Patrón de confirmación compartido ("Comisión registrada exitosamente") | p. 212: confirmación idéntica en todos los casos |

## E10 Cuotas por Carrera (CU 33, 34)

| CU | Diagrama / fuente | Qué dice | Qué se hizo | Por qué |
|---|---|---|---|---|
| 33 | Texto p. 241 | P2 "solicita el mes, año, monto y descripción" | Sin Descripción; combo Mes (clase Mes) | §7-7: corregir el texto de p. 241 |
| 33 | Spec: actor DG; lista de CU: CC – DG | — | Permisos de cuota para DG y CC | Lista de CU |
| 33 | Precondición "Carreras Activas" vs paso 2 "Pendiente o Activo" | — | Combo con carreras Pendiente o Activo | Paso 2 de las dos specs |
| 33 | Spec pasos 14–15 / 34 pasos 16–18 | Mostrar datos y pedir confirmación | El P2 y su botón son la confirmación | Estados P2 → P3 |
| 33 | Spec paso 10 / colaboración | Unicidad carrera + año + mes | `CuotaService::registrar` con bloqueo de la carrera (MariaDB sin índice parcial) | Concurrencia |
| 34 | Spec alt. 7.1 y 11.1 | Se validan al ingresar el año y al elegir la cuota | Al tocar ✎ (y otra vez al guardar) se muestra el aviso en P1 | P1 ya filtra por año; el ✎ es la selección |
| 34 | Spec paso 11 | "pagos registrados" | DetallePagoCuota de un Pago "Activo" | Un pago anulado no bloquea |
| 33/34 | Wireframe P1 | Estado "Activa" | Badge `Activo` | §7-11 |
| 33/34 | P3 | Sin wireframe | Confirmación compartida: "Cuota registrada con éxito" / "Cuota modificada con éxito" (texto del paso 18 / 20) | p. 212 |

## E11 Acta de Examen (CU 21, 22)

| CU | Diagrama / fuente | Qué dice | Qué se hizo | Por qué |
|---|---|---|---|---|
| 21/22 | Spec pasos 20 / 22 | "Docentes asignados a la Materia" | Todos los Docentes con cuenta `Activo` | §7-15 (no hay relación Docente–Materia) |
| 21 | Colaboración `generarNroActa` | Sin formato | `NumeradorService` (máximo + 1 con bloqueo, UNIQUE); se muestra con 4 dígitos ("0143") | §7-25; reutilizable para legajo y comprobante |
| 21/22 | Spec | Sin reglas para el tribunal | Al menos un docente; sin repetir docente ni rol (mensajes propios bajo la tabla) | Integridad (E11-D3) |
| 22 | Spec paso 32 "desvincula" | — | IntegranteTribunal → `Inactivo` (no se borra); cambiar el rol = quitar y agregar | Historial |
| 22 | Spec alt. 8.1 | Se valida al seleccionar el acta | Al tocar ✎ y otra vez al guardar; aviso en P1 | El ✎ es la selección |
| 21/22 | Wireframes P2 | Modalidad "Regular", Tipo de Acta "Final" | Valores de los catálogos (Presencial…; Regular/Libre) | §7-12 |
| 21/22 | P1 | ✎ en actas asentadas | ✎ deshabilitado ("Acta asentada"); 👁 y 🗑 deshabilitados (sin CU) | CU 22 solo actas "Pendiente" |
| 22 | P3 | Solo PNG de Registrar | Misma P3 con "Acta de Examen modificada exitosamente" (paso 34) | p. 212 |
| 21/22 | Spec pasos 32/33 | Notificar a los docentes | Correo encolado `TribunalActaExamen` (tribunal; en CU 22 también a los desvinculados) | Postcondición |
| CU 17 | Especificación paso 22 | Muestra el listado de Materias del año (Nombre, Código, Carga Horaria) | P2 sin listado: solo "Carga Horaria (calculada)"; las materias se vinculan automáticamente | Decisión de Nicolás E9-D1 (2026-10-26): manda el wireframe; ajustar el paso 22 en la tesis | E9 |

## E12 Preinscripción (CU1, con P2/P3 del CU 9)

| CU | Diagrama / fuente | Qué dice | Qué se hizo | Por qué |
|---|---|---|---|---|
| 1/9 | Estados CU1 (Fig. 61) "P3 Continuar / A: guarda Usuario y Encuesta" | Persiste en P3 | Se conserva en la sesión; Usuario, Encuesta, Alumno y Documentos se guardan en una sola transacción en P5 "Confirmar preinscripcion". La foto (P2) y los PDF (P4) van a carpetas temporales y se mueven al confirmar; una nueva búsqueda los descarta | Decisión E3-D7 (opción B) de Nicolás: sin usuarios huérfanos |
| 1 | Spec pasos 20–22 vs wireframes P4/P5 | Spec pide "Observaciones" y muestra "Constancia de Título" y "Observaciones"; P4/P5 no los tienen | **Agregado al prototipo por decisión de Nicolás (E12-D1 resuelta, 2026-10-26)**: P4 textarea opcional "Observaciones" al final de DOCUMENTACION (→ `alumnos.observaciones`); P5 sección CARRERA suma "Constancia de Titulo" (Si/No según se adjuntó el Certificado de Título) y "Observaciones" | **Pendiente en la tesis: agregar esos elementos a los wireframes P4 y P5 del CU1** (Figs. 65 y 66) |
| 1 | Spec pasos 5–7 | DNI existente: mostrar DNI, Nombre, Apellido, Barrio y pedir confirmación | P1 → P4 (diagrama de estados); P5 muestra el resumen con "Alumno registrado" | Manda el diagrama de estados (E12-D3) |
| 1 | — | DNI de un usuario del personal | Rechazo en P1: "El DNI ingresado corresponde a un usuario del personal" | Decisión E3-D11 de Nicolás (un rol por usuario) |
| 1 | — | Alumno ya registrado en la carrera | "El alumno ya se encuentra registrado en la carrera seleccionada." (mensaje propio) | `unique(user_id, carrera_id)` (E12-D2) |
| 1 | Spec paso 8 | Carreras "Activo" | Carreras "Activo" con plan de estudio Activo | Alumno 1..1 PlanEstudio (NOT NULL) |
| 1 | Spec paso 28 | Mensaje sin texto | "La preinscripción del alumno se registró correctamente." y vuelta a P1 | E12-D4 |
| 1 | P1 "Dato de prueba: 30123456 …" | Texto del wireframe | Se muestra fuera de producción; alumno@las.test tiene ese DNI en el DemoSeeder | Es un dato de demostración |
| 1 | P5 "1998-11-13", "ENCUESTA SOCIOECONOMICA" | Fecha ISO; lista solo respuestas "Si" | Fecha dd/mm/aaaa; solo las respuestas afirmativas, como el ejemplo | §1b formato argentino |
| 1 | Colaboración 10.2 `cargar(d)` | `d = fechaPresentacion` | También `nombre_documento` (= tipo), `archivo_documento` (disco privado) y `estado_documento` = Activo | Columnas NOT NULL del modelo |

## E13 Pago de Cuotas (CU7) y Caja (CU 35, 36)

| CU | Diagrama / fuente | Qué dice | Qué se hizo | Por qué |
|---|---|---|---|---|
| 7 | Spec paso 15 / P2 "Agregar" | Wireframe permite sumar importes por medio | Un solo medio por Pago: "Agregar" fija medio e importe (reemplaza); "Registrar Pago" con Saldo Pendiente 0; el servidor exige importe = Total ("El importe debe ser igual al Total a Pagar.", propio) | §7-5 (Nicolás) |
| 7 | Comprobante Fig. 88 "Factura C", CAE, código de barras | Factura electrónica AFIP | Comprobante interno `0001-XXXXXXXX`, QR a `validar/comprobante/{uuid}` en lugar del código de barras, CAE y Fch. Vto. "—"; datos fiscales en `config/las.php` | §7-6 |
| 7 | Colaboración `cargar(montoAplicado, montoBonificado)` | Sin regla | Beca "Activo" vigente (hoy entre inicio y fin); `valor_beca` = %; bonificado = monto × %; aplicado = monto − bonificado; `monto_total` = Σ aplicado | §7-8 |
| 7 | P2 columnas | Concepto / Fecha | Concepto = Mes en mayúsculas; Fecha = fecha de pago (pagadas) o vencimiento (a pagar, vacío para Inscripción); un preinscripto paga su Inscripción | §7-7 |
| 7 | Spec paso 3 / P1 Gestión de Alumnos | Listado de alumnos | P1 compartida (CU2, CU3, CU5, CU7); desde E14 "Registrar Alumno" abre CU1, "Inscribir →" (CU2) y Comision (CU3) habilitados; Examen (CU5) habilitado desde E17; 👁 y ⋮ fuera de alcance; el Alumno ve solo lo suyo | E13-D1, §7-10 |
| 7 | P2 "⋮" en las tablas | Sin función definida | Deshabilitado ("Fuera del alcance del prototipo") | §7-10 |
| 7 | Spec | No menciona correo | Sin correo al alumno | Skill §4 |
| 35/36 | P1 "Ver" | Solo describe el cierre de la caja Abierta | Caja Cerrada (o ajena): P2 de cierre en solo lectura con el detalle registrado y "Confirmar Cierre" deshabilitado | E13-D2 |
| 35/36 | Spec paso 10 / 19 | "Mensaje de apertura/cierre exitoso" sin texto | P3: "Apertura de Caja registrada exitosamente" / "Cierre de Caja registrado exitosamente" | E13-D2 |
| 36 | Colaboración de cierre | No carga `montoFinal` | `monto_final` = Σ montos contados | §7-24 |
| 36 | Spec paso 4 | Monto Esperado por medio | Todos los MedioPago vigentes (y los usados en la caja); Efectivo suma el Monto Inicial | Aclaración p. 162 |
| 35 | P1 "Abrir Caja" | Habilitado solo sin caja abierta | Deshabilitado con el tooltip "Ya tenés una caja abierta"; el acceso directo muestra el mismo aviso | Alt. 2.1 |
| 35 | P1 ejemplo "$0" | Monto inicial 0 | Se rechaza (spec alt. 5.1: "mayor a cero") | Manda la spec |

## E14 Inscripción (CU2)

| CU | Diagrama / fuente | Qué dice | Qué se hizo | Por qué |
|---|---|---|---|---|
| 2 | Spec pasos 16/19 y alt. 19 (texto actualizado por Nicolás) vs P2 "CUOTAS" (Marzo 2026 / Abril 2026) | Wireframe muestra cuotas mensuales | Se muestra solo "Inscripción <año>" con "✓ Pagada" / "! Pendiente"; alt. 19.1 "El alumno no registra el pago de la Cuota de Inscripción" en P1 (FIN). **Ajustar el ejemplo del wireframe P2 en la tesis** | §3 Cuotas y Mes, §7-7 |
| 2 | Spec "año lectivo" | Sin regla para elegirlo | Cuota de Inscripción de un año ≥ actual ya pagada; si no, la más próxima (`cuotaInscripcionVigente`) | E14-D1 |
| 2 | P1 (Fig. 68) pestañas Activos / Preinscriptos / **Eliminados** | Fig. 72 (CU3/CU5/CU7) no tiene Eliminados | Se mantiene la P1 compartida de la Fig. 72 (sin Eliminados). **Quitar "Eliminados" de la Fig. 68** | E14-D6 |
| 2 | P1 "Registrar Alumno" | Sin destino definido | Abre CU1 Preinscripción | E14-D2 |
| 2 | Spec pasos 5–12 | Documentos obligatorios | Obligatorios salvo que ya estén presentados en CU1 ("Adjuntado en la preinscripción"); adjuntar de nuevo reemplaza (el anterior queda Inactivo). Falta: "Adjunte el … en formato PDF." (propio) | E14-D3 |
| 2 | Colaboración | No incluye notificar | Correo `InscripcionRegistrada` (encolado, afterCommit) + link firmado de 48 h para definir la contraseña si no tiene | §4, §7-18 |
| 2 | — | Doble envío / alumno ya inscripto | "El alumno ya se encuentra inscripto." (propio) en P1 | E14-D4 |

## E15 Inscripción en Comisiones (CU3) y Cambio de Comisión (CU 19)

| CU | Diagrama / fuente | Qué dice | Qué se hizo | Por qué |
|---|---|---|---|---|
| 3 | Spec paso 5 | "al día con el pago de las cuotas" | Solo cuotas mensuales vencidas e impagas (nunca la Inscripción); alt. 5.2: lista "Cuota <Mes> <año>" en el modal de P1. El control está en la entrada a P2, que comparte el Cambio de Comisión | §3, §7-9, E15-D1 |
| 3 | Spec paso 2 | "Inscripto con Legajo Activo" | Si no: "El alumno no se encuentra en estado Inscripto con Legajo Activo." (propio) | E15-D2 |
| 3 | Spec paso 9 | Comisiones Activo de la carrera | Sin las que el alumno ya cursa con inscripciones Activo; capacidad = alumnos distintos con inscripción Activo | E15-D3 |
| 3 | Spec 14.1 | "indicando la Correlatividad que infringe" | Columna Estado: "No asignable: requiere <Materia> <Regular/Aprobada>" (P3 solo muestra "Habilitada") | Correlativa `p_cursar` + último EstadoCurricular (E15-D5) |
| 19 | Precondición "antes del registro de evaluaciones" | Sin mensaje | Bloqueo si alguna inscripción Activo de la comisión actual tiene notas o condición final: "No se puede cambiar la comisión porque el alumno ya tiene evaluaciones registradas en la comisión actual." **Texto propio, pendiente de Nicolás** | §7-26 |
| 19 | Estados Fig. 229 "P4 Confirmacion" | Sin PNG | Se reutiliza P4 de CU3: "Cambio de Comision registrado exitosamente", "Comision anterior / Comision nueva / Materias transferidas" | E15-D4 |
| 19 | Spec paso 11 | Confirmación con origen, destino y materias | Cambiar P3 ya los muestra; "Cambiar Comision" pide confirmación (SweetAlert) | §1b |
| 19 | — | Destino sin alguna materia a transferir | "La comisión de destino no dicta todas las materias del alumno en la comisión actual." (propio) | E15-D4 |
| 3/19 | Colaboración | No incluye notificar | Correo `ComisionAsignada` (código y horario) | §4 |

## E16 Regularidad Académica (CU4)

| CU | Diagrama / fuente | Qué dice | Qué se hizo | Por qué |
|---|---|---|---|---|
| 4 | P1 (Fig. 76) vs P1 de CU 17 | Columnas y acciones distintas | P1 propia de CU4 (`panel.regularidad.index`, menú "Regularidad Académica"); "Registrar Comision" solo con `crear_comision`; 🗑 fuera de alcance | E16-D1 |
| 4 | Actor Docente | Sin relación Docente–Comisión | El Docente ve todas las comisiones | §7-14 |
| 4 | DD `dec` notaCondicion | Sin regla | Promedio de las notas definitivas (el recuperatorio reemplaza a su parcial) | E16-D2 |
| 4 | Alt. 17 | Formato de nota sin definir | 1 a 10 con hasta 2 decimales (coma o punto) | §7-19 |
| 4 | P3 "Registrar Alumno", "Reporte", 🗑 | Sin CU | Deshabilitados "Fuera del alcance del prototipo" | §7-10 |
| 4 | Estados "Aceptar" de P5 | Destino no explícito | Vuelve a P3 de la materia | E16-D4 |

## E17 Inscripción a Exámenes (CU5)

| CU | Artefacto | Tesis | Prototipo | Motivo |
|---|---|---|---|---|
| 5 | Aclaración p. 72 / paso 14 | "Estado Curricular con condición Regular" y "correlatividades para rendir" | Sobre el historial de EstadoCurricular: Regular = algún "Regular" sin "Aprobado"; correlativa Regular = Regular o Aprobado, Aprobada = Aprobado (`TrayectoriaAcademicaService`, que también usa CU3) | E17-D1 |
| 5 | Alt. 13.2 / 14.2 | Listados sin formato | Renglones "Nombre · Mes Año" y "Materia: requiere Condición" en el modal de P1 | E17-D2 |
| 5 | Comprobante Fig. 82 | Sin QR | QR al pie a `validar/inscripcion-examen/{uuid}` (arquitectura §1) | E17-D3 |
| 5 | Estados Fig. 80 / P3 Confirmación | Sin wireframe | Patrón compartido de Confirmación con "Descargar Comprobante" y "Aceptar" | §5 skill |
| 5 | Colaboración Fig. 51 | Solo el Preceptor | Preceptor y Alumno (este solo sobre sus legajos, `AlumnoPolicy`) | §7-27, §7-18 |

## E18 Asentamiento de Acta (CU6)

| CU | Artefacto | Tesis | Prototipo | Motivo |
|---|---|---|---|---|
| 6 | Spec paso 20 / estados Fig. 83 "Guardar" | La nota se registra al confirmar | La nota, observación y condición se guardan en DetalleActa con "Guardar" de P3; EstadoCurricular, Trámite y acta al "Asentar Acta" | E18-D1 |
| 6 | Texto de la Fig. 85 | "solicita la nota, el estado (Aprobado, Desaprobado)" | Condición Final de solo lectura, calculada (nota ≥ 4), como dice la spec p. 74. **Ajustar el texto de la Fig. 85** | E18-D2 |
| 6 | P2 🗑 y "Reporte Listado Acta" | Sin comportamiento en la spec | Deshabilitados ("Fuera del alcance del prototipo") | E18-D3, §7-10 |
| 6 | P4 "Ausentes" / "Estado del Acta: Asentada" | No hay ausentes en la spec; el estado es "Finalizado" | Ausentes = inscriptos sin nota (siempre 0); se muestra "Asentada" como en el wireframe | E18-D4 |
| 6 | Paso 16 | Validación del folio sin regla | Entero 1–999999 no usado por otra acta | §7-19 |
| 6 | DD `ec` fechaCondicion | Sin regla | Fecha del acta | E18-D5 |
| 6 | P1 local de CU6 vs P1 del CU 21 | La de CU6 tiene columna "Estado" (Pendiente/"Cerrada"); la del CU 21 no | Se mantiene la P1 compartida del CU 21 (sin "Estado"). **Pendiente de Nicolás**: unificar las figuras | E18 (pendiente) |
| 6 | TituloTramite.tipoGestionTitulo 1..1 | CU6 no lo informa | null | §7-23 |
