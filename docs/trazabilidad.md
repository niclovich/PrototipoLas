# Trazabilidad: atributo de la tesis → columna

Lo mantiene el Agente Desarrollador al crear migraciones y modelos.

| Clase (tesis) | Atributo (tesis) | Tabla | Columna | Tipo | Notas |
|---|---|---|---|---|---|
| Usuario | idUsuario | users | id | bigint PK | |
| Usuario | dniUsuario | users | dni_usuario | string(15) unique | No se usa para entrar |
| Usuario | apellidoUsuario | users | apellido_usuario | string(100) | |
| Usuario | nombreUsuario | users | nombre_usuario | string(100) | |
| Usuario | fechaNacimiento | users | fecha_nacimiento | date null | |
| Usuario | sexo | users | sexo | string(20) null | Enum `Sexo`: Femenino, Masculino, X |
| Usuario | estadoCivil | users | estado_civil | string(30) null | Enum `EstadoCivil` |
| Usuario | telefonoUsuario | users | telefono_usuario | string(30) null | |
| Usuario | celularUsuario | users | celular_usuario | string(30) null | |
| Usuario | correoUsuario | users | email | string(150) unique | Login (Fortify `username`) |
| Usuario | direccionUsuario | users | direccion_usuario | string(200) null | |
| Usuario | barrioUsuario | users | barrio_usuario | string(100) null | |
| Usuario | paisUsuario | users | pais_usuario | string(80) null | |
| Usuario | fotoUsuario | users | foto_usuario | string(255) null | Ruta en disco `public` |
| Usuario | contrasenia | users | password | string null (hash) | Nulo hasta definirla con el link firmado |
| Usuario | fechaAlta | users | fecha_alta | date | |
| Usuario | estadoCuenta | users | estado_cuenta | string(20), Enum `EstadoCuenta` | `Activo` / `Inactivo` |
| Usuario | (asociación Localidad 1..1) | users | localidad_id | bigint FK null | Migración add_localidad_id_to_users (E1). Nullable para usuarios técnicos; Personal y Alumno la exigen en el FormRequest |
| — | (técnico) | users | email_verified_at, remember_token, created_at, updated_at | | Fortify / Laravel |
| Rol | idRol | roles | id | bigint PK | Tabla de Spatie |
| Rol | nombreRol | roles | name | string | Enum `RolSistema` para los 7 actores + SUPERADMIN |
| Rol | descripcionRol | roles | descripcion | string(255) null | |
| Rol | estadoRol | roles | estado | string(20), Enum `EstadoGeneral` | `Activo` / `Inactivo` |
| Usuario–Rol | (subclase de Usuario) | model_has_roles | role_id, model_id | pivote Spatie | Un rol por usuario (`syncRoles`) |

## Tablas de dominio (E1, generado desde el esquema de `sistema_las`)

| Clase (tesis) | Atributo (tesis) | Tabla | Columna | Tipo | Notas |
|---|---|---|---|---|---|
| Provincia | idProvincia | provincias | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Provincia | provincia | provincias | provincia | varchar(100) |  |
| Provincia | — | provincias | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| Localidad | idLocalidad | localidades | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Localidad | (asociación con Provincia) | localidades | provincia_id | bigint(20) unsigned |  |
| Localidad | localidad | localidades | localidad | varchar(100) |  |
| Localidad | — | localidades | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| Modalidad | idModalidad | modalidades | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Modalidad | modalidad | modalidades | modalidad | varchar(50) |  |
| Modalidad | — | modalidades | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| Duracion | idDuracion | duraciones | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Duracion | duracion | duraciones | duracion | varchar(50) |  |
| Duracion | cantidadAnios | duraciones | cantidad_anios | tinyint(3) unsigned | nuevo (plan §4) |
| Duracion | — | duraciones | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| Regimen | idRegimen | regimenes | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Regimen | regimen | regimenes | regimen | varchar(50) |  |
| Regimen | — | regimenes | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| Cuatrimestre | idCuatrimestre | cuatrimestres | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Cuatrimestre | cuatrimestre | cuatrimestres | cuatrimestre | varchar(50) |  |
| Cuatrimestre | — | cuatrimestres | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| Llamado | idLlamado | llamados | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Llamado | llamado | llamados | llamado | varchar(50) |  |
| Llamado | — | llamados | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| TipoActa | idTipoActa | tipos_acta | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| TipoActa | descripcion | tipos_acta | descripcion | varchar(50) |  |
| TipoActa | — | tipos_acta | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| RolTribunal | idRolTribunal | roles_tribunal | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| RolTribunal | descripcion | roles_tribunal | descripcion | varchar(50) |  |
| RolTribunal | — | roles_tribunal | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| TipoDocumento | idTipoDocumento | tipos_documento | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| TipoDocumento | descripcion | tipos_documento | descripcion | varchar(80) |  |
| TipoDocumento | — | tipos_documento | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| Motivo | idMotivo | motivos | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Motivo | descripcion | motivos | descripcion | varchar(150) |  |
| Motivo | accion | motivos | accion | varchar(50) |  |
| Motivo | — | motivos | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| MedioPago | idMedioPago | medios_pago | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| MedioPago | medioPago | medios_pago | medio_pago | varchar(50) |  |
| MedioPago | — | medios_pago | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| TipoGestionTitulo | idTipoGestionTitulo | tipos_gestion_titulo | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| TipoGestionTitulo | descripcion | tipos_gestion_titulo | descripcion | varchar(80) |  |
| TipoGestionTitulo | — | tipos_gestion_titulo | deleted_at | timestamp null | técnico (SoftDeletes, solo catálogos) |
| EncuestaSocioeconomica | idEncuestaSocioeconomica | encuestas_socioeconomicas | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| EncuestaSocioeconomica | (asociación con User) | encuestas_socioeconomicas | user_id | bigint(20) unsigned |  |
| EncuestaSocioeconomica | tieneHijos | encuestas_socioeconomicas | tiene_hijos | tinyint(1) |  |
| EncuestaSocioeconomica | viveSolo | encuestas_socioeconomicas | vive_solo | tinyint(1) |  |
| EncuestaSocioeconomica | padresViven | encuestas_socioeconomicas | padres_viven | tinyint(1) |  |
| EncuestaSocioeconomica | madreTrabaja | encuestas_socioeconomicas | madre_trabaja | tinyint(1) |  |
| EncuestaSocioeconomica | padreTrabaja | encuestas_socioeconomicas | padre_trabaja | tinyint(1) |  |
| EncuestaSocioeconomica | trabaja | encuestas_socioeconomicas | trabaja | tinyint(1) |  |
| EncuestaSocioeconomica | recibeAyudaEconomica | encuestas_socioeconomicas | recibe_ayuda_economica | tinyint(1) |  |
| EncuestaSocioeconomica | estudiosSuperioresPrevios | encuestas_socioeconomicas | estudios_superiores_previos | tinyint(1) |  |
| EncuestaSocioeconomica | poseeDiscapacidad | encuestas_socioeconomicas | posee_discapacidad | tinyint(1) |  |
| EncuestaSocioeconomica | miembroPoblacionIndigena | encuestas_socioeconomicas | miembro_poblacion_indigena | tinyint(1) |  |
| EncuestaSocioeconomica | hablanteLenguasIndigenas | encuestas_socioeconomicas | hablante_lenguas_indigenas | tinyint(1) |  |
| EncuestaSocioeconomica | sabeUsarPc | encuestas_socioeconomicas | sabe_usar_pc | tinyint(1) |  |
| EncuestaSocioeconomica | esUsuarioInternet | encuestas_socioeconomicas | es_usuario_internet | tinyint(1) |  |
| EncuestaSocioeconomica | nivelEstudiosMadre | encuestas_socioeconomicas | nivel_estudios_madre | varchar(50) null |  |
| EncuestaSocioeconomica | nivelEstudiosPadre | encuestas_socioeconomicas | nivel_estudios_padre | varchar(50) null |  |
| EncuestaSocioeconomica | idiomas | encuestas_socioeconomicas | idiomas | varchar(255) null |  |
| Carrera | idCarrera | carreras | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Carrera | nombreCarrera | carreras | nombre_carrera | varchar(150) |  |
| Carrera | resolucionCarrera | carreras | resolucion_carrera | varchar(50) |  |
| Carrera | estadoCarrera | carreras | estado_carrera | varchar(20) | Enum `EstadoCarrera` |
| Carrera | (asociación con Modalidad) | carreras | modalidad_id | bigint(20) unsigned |  |
| Carrera | (asociación con Duracion) | carreras | duracion_id | bigint(20) unsigned |  |
| Materia | idMateria | materias | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Materia | nombreMateria | materias | nombre_materia | varchar(150) |  |
| Materia | estadoMateria | materias | estado_materia | varchar(20) | Enum `EstadoGeneral` |
| PlanEstudio | idPlanEstudio | planes_estudio | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| PlanEstudio | (asociación con Carrera) | planes_estudio | carrera_id | bigint(20) unsigned |  |
| PlanEstudio | anioPlanEstudio | planes_estudio | anio_plan_estudio | smallint(5) unsigned |  |
| PlanEstudio | estadoPlanEstudio | planes_estudio | estado_plan_estudio | varchar(20) | Enum `EstadoGeneral` |
| DetallePlanEstudio | idDetallePlanEstudio | detalles_plan_estudio | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| DetallePlanEstudio | (asociación con PlanEstudio) | detalles_plan_estudio | plan_estudio_id | bigint(20) unsigned |  |
| DetallePlanEstudio | (asociación con Materia) | detalles_plan_estudio | materia_id | bigint(20) unsigned |  |
| DetallePlanEstudio | (asociación con Regimen) | detalles_plan_estudio | regimen_id | bigint(20) unsigned |  |
| DetallePlanEstudio | (asociación con Cuatrimestre) | detalles_plan_estudio | cuatrimestre_id | bigint(20) unsigned |  |
| DetallePlanEstudio | codigoDetPlanEstudio | detalles_plan_estudio | codigo_det_plan_estudio | varchar(20) |  |
| DetallePlanEstudio | cargaHorariaDetPlanEstudio | detalles_plan_estudio | carga_horaria_det_plan_estudio | smallint(5) unsigned |  |
| DetallePlanEstudio | anioCursoDetPlanEstudio | detalles_plan_estudio | anio_curso_det_plan_estudio | tinyint(3) unsigned |  |
| DetallePlanEstudio | estadoDetPlanEstudio | detalles_plan_estudio | estado_det_plan_estudio | varchar(20) | Enum `EstadoGeneral` |
| DetallePlanEstudio | fechaBaja | detalles_plan_estudio | fecha_baja | date null | de la colaboración de Baja de Materia del Plan |
| Correlativa | idCorrelativa | correlativas | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Correlativa | (asociación con DetallePlanEstudio) | correlativas | detalle_plan_estudio_id | bigint(20) unsigned |  |
| Correlativa | (asociación con DetallePlanCorrelativa) | correlativas | detalle_plan_correlativa_id | bigint(20) unsigned | idDetPlanCorrelativa |
| Correlativa | pCursar | correlativas | p_cursar | varchar(20) null | Enum `CondicionCorrelativa` |
| Correlativa | pRendir | correlativas | p_rendir | varchar(20) null | Enum `CondicionCorrelativa` |
| Comision | idComision | comisiones | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Comision | (asociación con Carrera) | comisiones | carrera_id | bigint(20) unsigned |  |
| Comision | (asociación con Modalidad) | comisiones | modalidad_id | bigint(20) unsigned |  |
| Comision | codigoComision | comisiones | codigo_comision | varchar(20) |  |
| Comision | capacidadComision | comisiones | capacidad_comision | smallint(5) unsigned |  |
| Comision | cargaHorariaComision | comisiones | carga_horaria_comision | smallint(5) unsigned |  |
| Comision | horarioComision | comisiones | horario_comision | varchar(100) |  |
| Comision | anioCurso | comisiones | anio_curso | tinyint(3) unsigned |  |
| Comision | anioLectivo | comisiones | anio_lectivo | smallint(5) unsigned |  |
| Comision | estadoComision | comisiones | estado_comision | varchar(20) | Enum `EstadoGeneral` |
| DetComision | idDetComision | det_comisiones | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| DetComision | (asociación con Comision) | det_comisiones | comision_id | bigint(20) unsigned |  |
| DetComision | (asociación con DetallePlanEstudio) | det_comisiones | detalle_plan_estudio_id | bigint(20) unsigned |  |
| DetComision | estadoDetComision | det_comisiones | estado_det_comision | varchar(20) | Enum `EstadoGeneral` |
| Alumno | idAlumno | alumnos | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Alumno | (asociación con User) | alumnos | user_id | bigint(20) unsigned |  |
| Alumno | (asociación con Carrera) | alumnos | carrera_id | bigint(20) unsigned |  |
| Alumno | (asociación con PlanEstudio) | alumnos | plan_estudio_id | bigint(20) unsigned |  |
| Alumno | (asociación con ComisionActual) | alumnos | comision_actual_id | bigint(20) unsigned null | asociación "cursa actualmente en" 0..1 |
| Alumno | nroLegajo | alumnos | nro_legajo | int(10) unsigned null |  |
| Alumno | estadoAlumno | alumnos | estado_alumno | varchar(30) | Enum `EstadoAlumno` |
| Alumno | estadoLegajo | alumnos | estado_legajo | varchar(20) null | Enum `EstadoLegajo` |
| Alumno | situacionEstudios | alumnos | situacion_estudios | varchar(20) | Enum `SituacionEstudios` |
| Alumno | adeudaMateria | alumnos | adeuda_materia | tinyint(1) |  |
| Alumno | observaciones | alumnos | observaciones | text null |  |
| DocumentoPresentado | idDocumentoPresentado | documentos_presentados | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| DocumentoPresentado | (asociación con User) | documentos_presentados | user_id | bigint(20) unsigned |  |
| DocumentoPresentado | (asociación con TipoDocumento) | documentos_presentados | tipo_documento_id | bigint(20) unsigned |  |
| DocumentoPresentado | nombreDocumento | documentos_presentados | nombre_documento | varchar(150) |  |
| DocumentoPresentado | fechaPresentacion | documentos_presentados | fecha_presentacion | date |  |
| DocumentoPresentado | archivoDocumento | documentos_presentados | archivo_documento | varchar(255) |  |
| DocumentoPresentado | estadoDocumento | documentos_presentados | estado_documento | varchar(20) | Enum `EstadoGeneral` |
| Novedad | idNovedad | novedades | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Novedad | (asociación con User) | novedades | user_id | bigint(20) unsigned |  |
| Novedad | (asociación con Alumno) | novedades | alumno_id | bigint(20) unsigned null |  |
| Novedad | (asociación con Motivo) | novedades | motivo_id | bigint(20) unsigned |  |
| Novedad | fechaNovedad | novedades | fecha_novedad | date |  |
| Novedad | observacionNovedad | novedades | observacion_novedad | text null |  |
| AlumnoComision | idAlumnoComision | alumno_comision | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| AlumnoComision | (asociación con Alumno) | alumno_comision | alumno_id | bigint(20) unsigned |  |
| AlumnoComision | (asociación con DetComision) | alumno_comision | det_comision_id | bigint(20) unsigned |  |
| AlumnoComision | fechaInscripcion | alumno_comision | fecha_inscripcion | date |  |
| AlumnoComision | estadoAlumnoComision | alumno_comision | estado_alumno_comision | varchar(20) | Enum `EstadoAlumnoComision` |
| AlumnoComision | tipoInscripcion | alumno_comision | tipo_inscripcion | varchar(20) | Enum `TipoInscripcion` |
| AlumnoComision | condicionFinal | alumno_comision | condicion_final | varchar(20) null | Enum `CondicionCursada` (null = "En Curso") |
| AlumnoComision | notaP1 | alumno_comision | nota_p1 | decimal(4,2) null |  |
| AlumnoComision | notaP2 | alumno_comision | nota_p2 | decimal(4,2) null |  |
| AlumnoComision | notaR1 | alumno_comision | nota_r1 | decimal(4,2) null |  |
| AlumnoComision | notaR2 | alumno_comision | nota_r2 | decimal(4,2) null |  |
| AlumnoComision | faltasAlumno | alumno_comision | faltas_alumno | smallint(5) unsigned null |  |
| EstadoCurricular | idEstadoCurricular | estados_curriculares | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| EstadoCurricular | (asociación con Alumno) | estados_curriculares | alumno_id | bigint(20) unsigned |  |
| EstadoCurricular | (asociación con DetallePlanEstudio) | estados_curriculares | detalle_plan_estudio_id | bigint(20) unsigned |  |
| EstadoCurricular | condicion | estados_curriculares | condicion | varchar(20) | Enum `CondicionCurricular` |
| EstadoCurricular | fechaCondicion | estados_curriculares | fecha_condicion | date |  |
| EstadoCurricular | notaCondicion | estados_curriculares | nota_condicion | decimal(4,2) null |  |
| EstadoCurricular | nroActa | estados_curriculares | nro_acta | int(10) unsigned null |  |
| Tribunal | idTribunal | tribunales | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| ActaExamen | idActaExamen | actas_examen | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| ActaExamen | nroActa | actas_examen | nro_acta | int(10) unsigned |  |
| ActaExamen | (asociación con DetallePlanEstudio) | actas_examen | detalle_plan_estudio_id | bigint(20) unsigned |  |
| ActaExamen | (asociación con Modalidad) | actas_examen | modalidad_id | bigint(20) unsigned |  |
| ActaExamen | (asociación con Llamado) | actas_examen | llamado_id | bigint(20) unsigned |  |
| ActaExamen | (asociación con TipoActa) | actas_examen | tipo_acta_id | bigint(20) unsigned |  |
| ActaExamen | (asociación con Tribunal) | actas_examen | tribunal_id | bigint(20) unsigned |  |
| ActaExamen | fechaActa | actas_examen | fecha_acta | date |  |
| ActaExamen | estadoActa | actas_examen | estado_acta | varchar(20) | Enum `EstadoActa` |
| ActaExamen | asentada | actas_examen | asentada | char(1) | Enum `Asentada` |
| ActaExamen | nroFolio | actas_examen | nro_folio | int(10) unsigned null |  |
| IntegranteTribunal | idIntegranteTribunal | integrantes_tribunal | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| IntegranteTribunal | (asociación con Tribunal) | integrantes_tribunal | tribunal_id | bigint(20) unsigned |  |
| IntegranteTribunal | (asociación con User) | integrantes_tribunal | user_id | bigint(20) unsigned |  |
| IntegranteTribunal | (asociación con RolTribunal) | integrantes_tribunal | rol_tribunal_id | bigint(20) unsigned |  |
| IntegranteTribunal | estadoIntegrante | integrantes_tribunal | estado_integrante | varchar(20) | Enum `EstadoGeneral` |
| DetalleActa | idDetalleActa | detalles_acta | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| DetalleActa | (asociación con ActaExamen) | detalles_acta | acta_examen_id | bigint(20) unsigned |  |
| DetalleActa | (asociación con Alumno) | detalles_acta | alumno_id | bigint(20) unsigned |  |
| DetalleActa | notaActa | detalles_acta | nota_acta | decimal(4,2) null |  |
| DetalleActa | observacion | detalles_acta | observacion | varchar(255) null |  |
| DetalleActa | estadoDetalleActa | detalles_acta | estado_detalle_acta | varchar(20) | Enum `EstadoDetalleActa` |
| DetalleActa | condicionFinal | detalles_acta | condicion_final | varchar(20) null | Enum `CondicionExamen` |
| DetalleActa | — | detalles_acta | uuid | char(36) | nuevo: identificador público para el QR |
| TituloTramite | idTituloTramite | titulos_tramite | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| TituloTramite | (asociación con Alumno) | titulos_tramite | alumno_id | bigint(20) unsigned |  |
| TituloTramite | (asociación con TipoGestionTitulo) | titulos_tramite | tipo_gestion_titulo_id | bigint(20) unsigned null |  |
| TituloTramite | fechaCreacion | titulos_tramite | fecha_creacion | date |  |
| TituloTramite | fechaInicio | titulos_tramite | fecha_inicio | date null |  |
| TituloTramite | fechaRecepcion | titulos_tramite | fecha_recepcion | date null |  |
| TituloTramite | fechaRetiro | titulos_tramite | fecha_retiro | date null |  |
| TituloTramite | estadoTramite | titulos_tramite | estado_tramite | varchar(20) | Enum `EstadoTramite` |
| TituloTramite | observacion | titulos_tramite | observacion | text null |  |
| Mes | idMes | meses | id | bigint(20) unsigned | PK; el id es el orden (1 Inscripción, 2 Marzo … 11 Diciembre) |
| Mes | descripcion | meses | descripcion | varchar(30) | unique; Inscripción se identifica por la constante `Mes::INSCRIPCION` |
| Cuota | idCuota | cuotas | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Cuota | (asociación con Carrera) | cuotas | carrera_id | bigint(20) unsigned |  |
| Cuota | (asociación con Mes, "corresponde a" 1..1) | cuotas | mes_id | bigint(20) unsigned | FK real a `meses.id` (Cuota 0..* — 1..1 Mes) |
| Cuota | monto | cuotas | monto | decimal(12,2) |  |
| Cuota | anio | cuotas | anio | smallint(5) unsigned |  |
| Cuota | estadoCuota | cuotas | estado_cuota | varchar(20) | Enum `EstadoGeneral` |
| TipoBeca | idTipoBeca | tipos_beca | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| TipoBeca | nombreBeca | tipos_beca | nombre_beca | varchar(100) |  |
| TipoBeca | tipoDescuento | tipos_beca | tipo_descuento | varchar(20) |  |
| TipoBeca | valorDescuento | tipos_beca | valor_descuento | decimal(12,2) |  |
| TipoBeca | requisitoAcademico | tipos_beca | requisito_academico | text null |  |
| TipoBeca | requisitoEconomico | tipos_beca | requisito_economico | text null |  |
| TipoBeca | estadoBeca | tipos_beca | estado_beca | varchar(20) | Enum `EstadoGeneral` |
| ConvocatoriaBeca | idConvocatoriaBeca | convocatorias_beca | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| ConvocatoriaBeca | (asociación con TipoBeca) | convocatorias_beca | tipo_beca_id | bigint(20) unsigned |  |
| ConvocatoriaBeca | anioLectivo | convocatorias_beca | anio_lectivo | smallint(5) unsigned |  |
| ConvocatoriaBeca | fechaDesde | convocatorias_beca | fecha_desde | date |  |
| ConvocatoriaBeca | fechaHasta | convocatorias_beca | fecha_hasta | date |  |
| ConvocatoriaBeca | cantidadDisponible | convocatorias_beca | cantidad_disponible | smallint(5) unsigned |  |
| ConvocatoriaBeca | estadoConvocatoria | convocatorias_beca | estado_convocatoria | varchar(20) | Enum `EstadoGeneral` |
| SolicitudBeca | idSolicitudBeca | solicitudes_beca | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| SolicitudBeca | (asociación con Alumno) | solicitudes_beca | alumno_id | bigint(20) unsigned |  |
| SolicitudBeca | (asociación con ConvocatoriaBeca) | solicitudes_beca | convocatoria_beca_id | bigint(20) unsigned |  |
| SolicitudBeca | situacionEconomica | solicitudes_beca | situacion_economica | text |  |
| SolicitudBeca | motivoSolicitud | solicitudes_beca | motivo_solicitud | text |  |
| SolicitudBeca | fechaSolicitud | solicitudes_beca | fecha_solicitud | date |  |
| SolicitudBeca | estadoSolicitud | solicitudes_beca | estado_solicitud | varchar(20) |  |
| SolicitudBeca | motivoRechazo | solicitudes_beca | motivo_rechazo | text null |  |
| Beca | idBeca | becas | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Beca | (asociación con Alumno) | becas | alumno_id | bigint(20) unsigned |  |
| Beca | (asociación con ConvocatoriaBeca) | becas | convocatoria_beca_id | bigint(20) unsigned |  |
| Beca | (asociación con SolicitudBeca) | becas | solicitud_beca_id | bigint(20) unsigned |  |
| Beca | fechaInicio | becas | fecha_inicio | date |  |
| Beca | fechaFin | becas | fecha_fin | date |  |
| Beca | valorBeca | becas | valor_beca | decimal(12,2) |  |
| Beca | estadoBeca | becas | estado_beca | varchar(20) | Enum `EstadoGeneral` |
| Beca | motivoBaja | becas | motivo_baja | text null |  |
| Caja | idCaja | cajas | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Caja | (asociación con User) | cajas | user_id | bigint(20) unsigned |  |
| Caja | fechaApertura | cajas | fecha_apertura | datetime |  |
| Caja | montoInicial | cajas | monto_inicial | decimal(12,2) |  |
| Caja | fechaCierre | cajas | fecha_cierre | datetime null |  |
| Caja | montoFinal | cajas | monto_final | decimal(12,2) null |  |
| Caja | diferenciaTotal | cajas | diferencia_total | decimal(12,2) null |  |
| Caja | observacionCierre | cajas | observacion_cierre | text null |  |
| Caja | estadoCaja | cajas | estado_caja | varchar(20) | Enum `EstadoCaja` |
| DetalleCierreCaja | idDetalleCierreCaja | detalles_cierre_caja | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| DetalleCierreCaja | (asociación con Caja) | detalles_cierre_caja | caja_id | bigint(20) unsigned |  |
| DetalleCierreCaja | (asociación con MedioPago) | detalles_cierre_caja | medio_pago_id | bigint(20) unsigned | un solo medio por pago (decisión §7-5) |
| DetalleCierreCaja | montoEsperado | detalles_cierre_caja | monto_esperado | decimal(12,2) |  |
| DetalleCierreCaja | montoContado | detalles_cierre_caja | monto_contado | decimal(12,2) |  |
| DetalleCierreCaja | diferencia | detalles_cierre_caja | diferencia | decimal(12,2) |  |
| Pago | idPago | pagos | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| Pago | (asociación con Alumno) | pagos | alumno_id | bigint(20) unsigned |  |
| Pago | (asociación con Caja) | pagos | caja_id | bigint(20) unsigned |  |
| Pago | (asociación con MedioPago) | pagos | medio_pago_id | bigint(20) unsigned | un solo medio por pago (decisión §7-5) |
| Pago | fechaPago | pagos | fecha_pago | datetime |  |
| Pago | montoTotal | pagos | monto_total | decimal(12,2) |  |
| Pago | estadoPago | pagos | estado_pago | varchar(20) | Enum `EstadoGeneral` |
| DetallePagoCuota | idDetallePagoCuota | detalles_pago_cuota | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| DetallePagoCuota | (asociación con Pago) | detalles_pago_cuota | pago_id | bigint(20) unsigned |  |
| DetallePagoCuota | (asociación con Cuota) | detalles_pago_cuota | cuota_id | bigint(20) unsigned |  |
| DetallePagoCuota | (asociación con Beca) | detalles_pago_cuota | beca_id | bigint(20) unsigned null |  |
| DetallePagoCuota | montoAplicado | detalles_pago_cuota | monto_aplicado | decimal(12,2) |  |
| DetallePagoCuota | montoBonificado | detalles_pago_cuota | monto_bonificado | decimal(12,2) |  |
| FacturaComprobante | idFacturaComprobante | facturas_comprobante | id | bigint(20) unsigned | PK (la tesis usa id<Clase>) |
| FacturaComprobante | (asociación con Pago) | facturas_comprobante | pago_id | bigint(20) unsigned |  |
| FacturaComprobante | numeroComprobante | facturas_comprobante | numero_comprobante | int(10) unsigned |  |
| FacturaComprobante | fechaEmision | facturas_comprobante | fecha_emision | date |  |
| FacturaComprobante | estadoFactura | facturas_comprobante | estado_factura | varchar(20) | Enum `EstadoGeneral` |
| FacturaComprobante | — | facturas_comprobante | uuid | char(36) | nuevo: identificador público para el QR |

## Pantallas de Personal (E2) → código

| Pantalla (wireframe) | Vista | Controller / ruta | Notas |
|---|---|---|---|
| P1 Gestión de Personal — Activos / Eliminados | `resources/views/panel/personal/index.blade.php`, `_acciones.blade.php` | `PersonalController@index`, `@datatable` (`panel.personal.index`, `panel.personal.datatable`) | `PersonalDataTable` con filtros `buscar` y `rol` |
| P2 Registrar Personal + P3 DNI ya registrado + P4 Contraseña inválida | `registrar.blade.php`, `_form.blade.php`, `<x-modal-aviso>` | `@create`, `@store`, `@verificarDni` | Localidades: `panel.personal.localidades` |
| P2 Modificar Datos del Personal + Datos inválidos | `modificar.blade.php`, `_form.blade.php` | `@edit`, `@update` | DNI de solo lectura |
| P2 Modal Registrar Baja de Personal | `_modal-baja.blade.php` | `@registrarBaja` (`panel.personal.baja`) | Crea Novedad con Motivo "Baja de Personal" |
| P5 / P3 Confirmación | `confirmacion.blade.php` | `@confirmacion` | Mensaje en sesión |
| Usuario.ultimaBaja (derivado) | — | `User::ultimaBaja()` | Última Novedad con Motivo de acción "Baja de Personal" (FECHA BAJA, MOTIVO DE BAJA) |

## Usuarios Alumno (E3, CU 9)

| Clase (tesis) | Atributo (tesis) | Tabla | Columna | Tipo | Notas |
|---|---|---|---|---|---|
| Usuario | paisUsuario | users | pais_usuario | string(80) null | CU 9: `config('las.pais_por_defecto')` ("Argentina"); P2 no tiene el campo (D4) |
| Usuario | fotoUsuario | users | foto_usuario | string(255) null | Ruta en el disco `public`, carpeta `fotos-usuarios/` |
| EncuestaSocioeconomica | nivelEstudiosMadre / nivelEstudiosPadre | encuestas_socioeconomicas | nivel_estudios_madre / nivel_estudios_padre | string(50) null | Enum `NivelEstudios` |
| EncuestaSocioeconomica | idiomas | encuestas_socioeconomicas | idiomas | string(255) null | Lista del Enum `Idioma`, separada por ", " (el modelo la lee como array) |
| CU 9 | Registrar Usuario Alumno (pasos 9–10) | users + encuestas_socioeconomicas | — | — | `App\Services\Alumnos\UsuarioAlumnoService::registrar()`; contrato de P2/P3 en `DatosPersonalesAlumnoRequest` y `EncuestaSocioeconomicaRequest` |

## Pantallas de Carrera (E4) → código

| Pantalla (wireframe) | Vista | Controller / ruta | Notas |
|---|---|---|---|
| P1 Gestión de Carreras (`Registrar_P1.png`) | `resources/views/panel/academico/carreras/index.blade.php`, `_acciones.blade.php` | `CarreraController@index`, `@datatable` (`panel.carreras.index`, `panel.carreras.datatable`) | `CarreraDataTable`; filtro `buscar` → `Carrera::scopeBuscar` |
| P2 Registrar Carrera (`Registrar_P2.png`) | `registrar.blade.php` + `_form.blade.php` | `@create`, `@store` | Modales "Carrera ya existente" / "Resolución inválida" (`<x-modal-aviso-errores>`) |
| P2 Modificar Carrera (`Modificar_P2.png`) | `modificar.blade.php` + `_form.blade.php` | `@edit`, `@update` | Con Plan de Estudio activo solo se habilita el Estado |
| P3 Confirmación (`Registrar_P3.png`) | `resources/views/panel/_confirmacion.blade.php` | `@confirmacion` | Título + 3 líneas, en sesión (`ConPantallaConfirmacion`) |
| Carrera.resolucion_texto (derivado) | — | `Carrera::resolucionTexto()` | "Resolución N° " + `resolucion_carrera` |

## Pantallas de Materia (E5) → código

| Pantalla (wireframe) | Vista | Controller / ruta | Estado / notas |
|---|---|---|---|
| P1 Gestión de Materias (`P1.png`) | `resources/views/panel/academico/materias/index.blade.php`, `_acciones.blade.php` | `MateriaController@index`, `@datatable` (`panel.materias.index`, `panel.materias.datatable`) | `MateriaDataTable`; filtro `buscar`. Implementada, revisión visual pendiente |
| P2 Registrar Materia (`P2.png`) | `registrar.blade.php` + `_form.blade.php` | `@create`, `@store` | E5-D1 resuelta por Nicolás (2026-10-26): Estado visible con `Activo`, select deshabilitado y valor forzado por el servidor. El servicio traduce una colisión SQL de nombre al mismo error de validación |
| P2 Modificar Materia (`P3.png`, numerado P2 por la tesis) | `modificar.blade.php` + `_form.blade.php` | `@edit`, `@update` | D2 resuelta por Nicolás (2026-10-26): abre P2 y controla el vínculo al guardar; si está vinculada vuelve a P1 sin modificar. Una colisión SQL de nombre también conserva el mensaje exacto de la spec |
| P3 Confirmación (sin PNG específico) | `resources/views/panel/_confirmacion.blade.php` | `@confirmacion` | Título + Materia + Estado, mediante `ConPantallaConfirmacion` |
| Modal materia vinculada | `<x-modal-aviso>` en `index.blade.php` | aviso de `MateriaController` | Mensaje exacto centralizado en `MateriaService`; se abre después del intento de guardar bloqueado (D2) |

## Pantallas de Plan de Estudio (E6–E8) → código

| Pantalla (wireframe) | Vista | Controller / ruta | Notas |
|---|---|---|---|
| P1c / P1 Gestión de Plan de Estudio (`Registrar_Plan_P1c.png`, `Registrar_Materia_P1.png`) | `resources/views/panel/academico/planes/index.blade.php`, `_sin-plan`, `_con-plan`, `_acciones`, `_correlativas` | `PlanEstudioController@index`, `@datatable` | `DetallePlanEstudioDataTable` (detalles Activo); combos recargan P1 (`plan-estudio.js`) |
| CU 5 P2 Registrar Plan (`Registrar_Plan_P1b.png`) | `planes/registrar.blade.php` | `PlanEstudioController@create`, `@store` | Modal "Año inválido" |
| CU 6 P2 Registrar Materia al Plan (`Registrar_Materia_P2.png`) | `planes/materias/registrar.blade.php` | `DetallePlanEstudioController@create`, `@store` | Modales alt. 8, 11, 23 |
| CU 7 P2 Modal Dar de Baja (`Baja_Materia_P2.png`) | `planes/_modal-baja-materia.blade.php` | `DetallePlanEstudioController@registrarBaja` | Aviso alt. 6 `modal-comisiones-dictandose` |
| CU 8 P2 Registrar Correlatividades (`Correlatividades_P2.png`) | `planes/correlatividades/registrar.blade.php` | `CorrelatividadController@create`, `@store` | Tabla armada en el navegador; se guarda completa |
| P3 Confirmación (CU 5–8) | `panel/_confirmacion.blade.php` | `PlanEstudioController@confirmacion` | Título + líneas en sesión; "Aceptar" → P1 con la carrera |
| Cuatrimestre.abreviatura (derivado) | — | `Cuatrimestre::abreviatura()` | "1er Cuatrimestre" → "1C" |

Sin columnas nuevas: `planes_estudio`, `detalles_plan_estudio` (incluida `fecha_baja`) y `correlativas` ya existían (E1).

## E9–E11 Comisión, Cuotas por Carrera y Acta de Examen
Sin columnas nuevas: `comisiones`, `det_comisiones`, `cuotas`, `actas_examen`, `tribunales` e `integrantes_tribunal` ya existían (E1). Derivados: `ActaExamen::nro_acta_texto` (4 dígitos), `Asentada::label()` ("Si"/"No"), `App\Support\Moneda::formatear` ("$45.000"). Numeración: `App\Services\Numeracion\NumeradorService`.

## E12 Preinscripción (CU1)
Sin columnas nuevas. Alumno.observaciones = campo "Observaciones" de P4 (E12-D1 resuelta: agregado). DocumentoPresentado: `archivo_documento` en el disco `local` (`documentos/{user_id}/`); `nombre_documento` = `TipoDocumento::FOTOCOPIA_DNI` / `CERTIFICADO_TITULO`. Etiquetas de la encuesta: `EncuestaSocioeconomica::SECCIONES`.

## E13 Pago de Cuotas y Caja (CU7, 35, 36)
Sin columnas nuevas. Pago.monto_total = Σ DetallePagoCuota.monto_aplicado; DetallePagoCuota.beca_id solo si hubo bonificación. FacturaComprobante.numero_comprobante con `NumeradorService` (visible `numeroFormateado()`), `uuid` para `validar/comprobante/{uuid}`. Caja.monto_final = Σ DetalleCierreCaja.monto_contado; `MedioPago::EFECTIVO`.

## E14 Inscripción (CU2)
Sin columnas nuevas. Alumno.nro_legajo con `NumeradorService` (desde `LAS_LEGAJO_INICIAL`); estado_alumno = Inscripto, estado_legajo = Activo. DocumentoPresentado nuevo por documento adjuntado (el anterior del mismo tipo pasa a Inactivo). Cuota de Inscripción del año lectivo: `DeudaCuotasService::cuotaInscripcionVigente()`.

## E15 Inscripción en Comisiones y Cambio de Comisión (CU3, CU 19)
Sin columnas nuevas. AlumnoComision: fecha_inscripcion = hoy, estado Activo/Caducado, tipo "Por Comisión". Alumno.comision_actual_id = comisión inscripta / de destino.

## E16 Regularidad Académica (CU4)
Sin columnas nuevas. AlumnoComision.nota_p1..nota_r2, faltas_alumno, condicion_final (Regular/Libre; null = "En Curso"). EstadoCurricular nuevo por registro: condicion = condicion_final, fecha_condicion = hoy, nota_condicion = promedio de las notas definitivas (E16-D2), nro_acta = null.

## E17 Inscripción a Exámenes (CU5)
Sin columnas nuevas. DetalleActa: acta_examen_id, alumno_id, estado_detalle_acta = "Activo", uuid (QR del comprobante); nota_acta, observacion y condicion_final null hasta CU6.

## E18 Asentamiento de Acta (CU6)
Sin columnas nuevas. DetalleActa.notaActa → nota_acta (1–10, 2 decimales), observacion, condicionFinal → condicion_final (Aprobado/Desaprobado calculado). EstadoCurricular: condicion = condicion_final, fecha_condicion = fecha del acta, nota_condicion = nota_acta, nro_acta. ActaExamen: nro_folio, estado_acta = "Finalizado", asentada = "S". TituloTramite: fecha_creacion = hoy, estado_tramite = "Sin Gestión", tipo_gestion_titulo_id null; Alumno.estado_alumno = "Título en Trámite".

