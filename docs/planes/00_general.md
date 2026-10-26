# Plan general del prototipo LAS (00_general)

Autor: Agente Planificador. Fecha: 2026-10-25. Estado: **borrador para aprobación de Nicolás**.
Reglas: skill `prototipo-tesis-las` (§0–§9). La arquitectura de referencia es la §1 de la skill (no existe `docs/arquitectura.md`).
Fuentes leídas: `Tesis_LAS.pdf` (262 páginas, texto completo + imágenes de pp. 97, 100, 101, 123, 158, 161 y 232), `diseno_general/diagrama_clases_diseno_4_hojas_corregido.drawio`, todos los `.drawio` de `CU1…CU7` y `soporte/*`, wireframes PNG relevantes, esqueleto Laravel 12.70.1.

> **Nomenclatura de anexos.** El pedido habla de "Anexo 1/2/3"; en el PDF se llaman **Anexo A** (especificaciones, p. 107), **Anexo B** (colaboración + clases de diseño, p. 172), **Anexo C** (diseño estático y dinámico: estados + wireframes, p. 212) y **Anexo D** (prototipo, p. 261). Los 7 CU principales NO están en los anexos: su especificación está en el cuerpo (cap. 8, pp. 61–77), su colaboración en el cap. 9 Iteraciones 1–2 (pp. 79–87) y sus estados/wireframes en el cap. 9 Iteración 3 (pp. 91–104).
> **Los números de figura del índice (pp. 9–12) están desfasados respecto del cuerpo** (p. ej. el índice dice "Figura 57 Diagrama de Clases de Diseño Final Completo" y en el cuerpo la 57 es la Hoja 1). Usar siempre los **números de página** de este documento.

---

## 1. Índice de la tesis

Páginas = número de página del PDF (coincide con el pie de página impreso).

### 1.1 Documentos generales

| Contenido | Páginas |
|---|---|
| Listado de casos de uso con actores | 58–59 |
| Modelo del dominio inicial (Fig. 35) | 60 |
| Diagrama de clases de diseño, 4 hojas (Figs. 57–60): H1 Usuarios y Personal, H2 Alumnos y Académico, H3 Exámenes y Equivalencias, H4 Becas y Finanzas | 87–91 |
| Anexo D: alcance del prototipo y tecnologías | 261–262 |
| Gestionar Trámite de Título (fuera de alcance; define los estados de TituloTramite) | spec 154–156, colab 205–206, wf 252–253 |

### 1.2 CU principales (Anexo D)

| CU prototipo | Nº en la tesis | Nombre | Actor(es) en la lista (p. 58–59) | Actor en la especificación | Especificación + análisis | Colaboración + diseño | Estados + wireframes | Archivos locales |
|---|---|---|---|---|---|---|---|---|
| CU1 | 10 | Registrar datos de Preinscripción de Alumno | AA – PR | Preceptor / Auxiliar Administrativo | 61–63 (análisis Fig. 36 p. 63) | 79–81 (colab Fig. 43 p. 80; diseño Fig. 44 p. 81) | 92–94 (estados Fig. 61 p. 92; P1 Buscar Alumno p. 92; P2 Datos Personales p. 93; P3 Encuesta p. 93; P4 Carrera y Documentación p. 94; P5 Resumen/Confirmar p. 94) | `CU1_preinscripcion/` completo |
| CU2 | 11 | Registrar datos de Inscripción de Alumno | AA – PR | Preceptor / Auxiliar Administrativo | 63–65 (Fig. 37 p. 65) | 81–82 (Figs. 45 p. 81, 46 p. 82) | 95–96 (estados Fig. 67 p. 95; P1 Listado Preinscriptos p. 95; P2 Formulario p. 95; P3 Confirmación p. 96) | `CU2_inscripcion/` completo |
| CU3 | 18 | Registrar datos de Inscripción de Alumno en Comisiones | PR | Preceptor | 65–68 (Fig. 38 p. 68) | 82–83 (Figs. 47 p. 82, 48 p. 83) | 96–98 (estados Fig. 71 p. 96; P1 Gestión de Alumnos p. 97; P2 Historial de comisiones p. 97; P3 Formulario p. 98) | `CU3_inscripcion_comision/` |
| CU3 (cambio) | 19 | Registrar datos de Cambio de Comisión | PR | Preceptor | Anexo A 136–137 (Fig. 104 p. 137) | Anexo B 189–190 (Figs. 152, 153) | Anexo C 233–234 (estados Fig. 229 p. 233; P2 Historial p. 233; P3 Formulario Cambiar Comisión p. 234) | `*_cambio_comision.drawio`, `wireframes/Cambiar_P3.png` (ignorar la 2.ª página de `clases_analisis_cambio_comision` = Actas y de `estados_cambio_comision` = Plan) |
| CU4 | 20 | Registro de Regularidad Académica por Materia | PR – DC | Preceptor / Docente | 68–70 (Fig. 39 p. 70; aclaración Condición Final pp. 69–70) | 84 (Figs. 49, 50) | 98–100 (estados Fig. 75 p. 98; P1 Gestión de Comisiones p. 99; P2 Materias de la Comisión p. 99; P3 Alumnos Inscriptos p. 99; P4 Formulario p. 100) | `CU4_regularidad/` completo |
| CU5 | 23 | Registrar Inscripción de Alumnos en Exámenes | PR – AL | Alumno / Preceptor (la colaboración solo dibuja Preceptor) | 70–72 (Fig. 40 p. 72; aclaración condiciones p. 72) | 84–85 (Figs. 51, 52 p. 85) | **Sí existen en el PDF**: estados Fig. 80 p. 100; P1 = Gestión de Alumnos Fig. 72 p. 97 (reutilizada); P2 Actas de Examen Disponibles Fig. 81 p. 101; Comprobante de Inscripción Fig. 82 p. 101 | Falta `estados` y `wireframes/` locales (ver §7, punto 30) |
| CU6 | 24 | Registrar datos de Asentamiento del Acta | PR | Preceptor | 72–75 (Fig. 41 p. 75; aclaraciones nota y plan completo p. 74) | 85–86 (Figs. 53 p. 85, 54 p. 86) | 102–103 (estados Fig. 83 p. 102; P1 = Gestión de Actas Examen del CU 21, p. 235; P2 Alumnos Inscriptos al Acta p. 102; P3 Formulario Nota p. 103) | `CU6_asentamiento_acta/` completo |
| CU7 | 37 | Registrar Pagos de Cuotas | CJ | Cajero | 75–77 (Fig. 42 p. 77) | 86–87 (Figs. 55, 56 p. 87) | 103–104 (estados Fig. 86 p. 103; P1 = Gestión de Alumnos p. 97; P2 Registrar Pago p. 104; Comprobante de Pago Fig. 88 p. 104) | `CU7_pago_cuotas/` completo |

### 1.3 CU de soporte (§6)

| Nº | Nombre | Actor lista | Actor spec / colab | Especificación + análisis (Anexo A) | Colaboración + diseño (Anexo B) | Estados + wireframes (Anexo C) | Local |
|---|---|---|---|---|---|---|---|
| 25 | Registrar datos del Personal | DG | DG | 142–144 (Fig. 107 p. 144) | 193–194 (Figs. 158, 159) | 237–238 (estados; P1 Gestión de Personal p. 237; P2 Formulario p. 238) | `soporte/personal/` |
| 26 | Modificar Datos del Personal | DG | DG | 144–145 (Fig. 108 p. 145) | 195–196 (Figs. 162, 163) | 238–239 (estados; P2 p. 239) | idem |
| 27 | Registrar Baja del Personal (opcional) | DG | DG | 146–147 (Fig. 109 p. 147) | 194–195 (Figs. 160, 161) | 239–240 (estados; P2 Modal p. 240) | idem (+ pestaña Eliminados) |
| 9 | Registrar Datos de Usuarios Alumno | PR | spec: Preceptor / Coordinador | 122–123 (Fig. 97 p. 123) | **no tiene** | **no tiene**: sus pantallas son P2 y P3 del CU1 (texto p. 93) | `soporte/usuario_alumno/` solo análisis "solo atributos" |
| 3 | Registrar datos de una nueva Carrera | CC | CC | 110–111 (Fig. 91 p. 112) | 174–175 (Figs. 128, 129) | 214–215 (estados; P1 Gestión de Carreras p. 214; P2 Formulario p. 215) | `soporte/carrera/` |
| 4 | Modificar datos de una Carrera | CC | CC | 112–113 (Fig. 92 p. 113) | 175–176 (Figs. 130, 131) | 215–216 (estados; P2 p. 216) | idem |
| 1 | Registrar datos de nueva Materia | CC | CC | 108 (Fig. 89 p. 109) | 172–173 (Figs. 124, 125) | 212–213 (estados; P1 Gestión de Materias p. 212; P2 p. 213) | `soporte/materia/` |
| 2 | Modificar datos de una Materia | CC | CC | 109–110 (Fig. 90 p. 110) | 173–174 (Figs. 126, 127) | 213–214 (estados; P2 p. 214) | idem |
| 5 | Registrar Plan de Estudio | CC | CC | 113–114 (Fig. 93 p. 114) | 176–177 (Figs. 132, 133) | 216–217 (estados; P1c sin plan p. 216; P2 p. 217) | `soporte/plan_estudio/` |
| 6 | Registrar Materia al Plan de Estudio | CC | CC | 114–116 (Fig. 94 p. 117) | 177–178 (Figs. 134, 135) | 217–218 (estados; P1 con plan p. 218; P2 p. 218) | idem |
| 7 | Registrar Baja de Materia del Plan (opcional) | CC | CC | 117–118 (Fig. 95 p. 119) | 178–179 (Figs. 136, 137) | 219–220 (estados; P2 Modal p. 220) | idem |
| 8 | Registrar Correlatividades de Materia | CC | CC | 120–121 (Fig. 96 p. 121) | 179–180 (Figs. 138, 139) | 220–221 (estados; P2 p. 221) | `soporte/correlatividades/` |
| 17 | Registrar datos de una nueva Comisión | CC | CC | 133–135 (Fig. 103 p. 136) | 188–189 (Figs. 150, 151) | **Sí existen en el PDF**: p. 232 (estados Fig. 226; P1 Gestión de Comisiones Fig. 227; P2 Formulario Fig. 228) | `soporte/comision/` sin `wireframes/` |
| 21 | Registrar datos de Acta de Examen | CC | CC | 138–139 (Fig. 105 p. 139) | 190–191 (Figs. 154, 155) | 234–235 (estados; P1 Gestión de Actas p. 235; P2 p. 235) | `soporte/acta_examen/` |
| 22 | Modificar datos de Acta de Examen | CC | CC | 140–142 (Fig. 106 p. 142) | 191–192 (Figs. 156, 157) | 236 (estados; P2 p. 236) | idem |
| 33 | Registrar datos de Cuotas por Carrera | CC – DG | spec: DG; colab: CC/DG | 156–158 (análisis Fig. 115 p. 158; aclaración vencimiento p. 158) | 196–197 (Figs. 164, 165) | 240–241 (estados; P1 Gestión de Cuotas p. 241; P2 p. 241) | `soporte/cuotas/` (sin análisis "registrar" local; está en p. 158) |
| 34 | Modificar datos de Cuotas por Carrera | CC – DG | spec y colab: DG | 158–159 (Fig. 116 p. 160) | 197–198 (Figs. 166, 167) | 241–242 (estados; P2 p. 242) | idem |
| 35 | Registrar datos de Apertura de Caja (parte de CU7) | CJ | CJ | 160–161 (análisis Fig. 117 p. 161) | 198–199 (Figs. 168, 169) | 242–243 (estados; P1 Gestión de Cajas p. 243; P2 p. 243) | `soporte/caja/` (sin análisis apertura local; está en p. 161) |
| 36 | Registrar datos de Cierre de Caja (parte de CU7) | CJ | CJ | 161–163 (Fig. 118 p. 164; aclaración cálculo pp. 162–163) | 199–200 (Figs. 170, 171) | 243–244 (estados; P2 p. 244) | idem |

Catálogos sin CU (solo seeder): Modalidad, Duración, Régimen, Cuatrimestre, Llamado, TipoActa, RolTribunal, TipoDocumento, Provincia/Localidad, Motivo, MedioPago, TipoGestionTitulo, Mes.

---

## 2. Roles, permisos y menú

### 2.1 Actores → roles (Spatie, un rol por usuario con `syncRoles`)

| Sigla tesis | Clase (Hoja 1) | Rol (`roles.name`) | Enum `App\Enums\RolSistema` |
|---|---|---|---|
| DG | DirectorGeneral | `Director General` | `DirectorGeneral` |
| CC | Coordinador | `Coordinador` | `Coordinador` |
| PR | Preceptor | `Preceptor` | `Preceptor` |
| AA | AuxiliarAdministrativo | `Auxiliar Administrativo` | `AuxiliarAdministrativo` |
| DC | Docente | `Docente` | `Docente` |
| CJ | Cajero | `Cajero` | `Cajero` |
| AL | Alumno (Usuario con Alumno) | `Alumno` | `Alumno` |
| — | (técnico) | `SUPERADMIN` | `SuperAdmin` (todo vía `Gate::before`) |

La restricción de la Hoja 1 "El Rol del Usuario debe corresponder a su subclase" se cumple porque la subclase **es** el rol (no hay tablas por subclase; ver §4).

### 2.2 Permisos `{accion}_{entidad}` (52)

| # | Permiso | Uso (CU / pantalla) | DG | CC | PR | AA | DC | CJ | AL |
|---|---|---|---|---|---|---|---|---|---|
| 1 | `listar_carrera` | P1 Gestión de Carreras (3, 4) | | ✔ | | | | | |
| 2 | `crear_carrera` | CU 3 | | ✔ | | | | | |
| 3 | `editar_carrera` | CU 4 | | ✔ | | | | | |
| 4 | `listar_materia` | P1 Gestión de Materias (1, 2) | | ✔ | | | | | |
| 5 | `crear_materia` | CU 1 | | ✔ | | | | | |
| 6 | `editar_materia` | CU 2 | | ✔ | | | | | |
| 7 | `listar_plan_estudio` | P1/P1c Gestión de Plan de Estudio (5–8) | | ✔ | | | | | |
| 8 | `crear_plan_estudio` | CU 5 | | ✔ | | | | | |
| 9 | `crear_materia_plan` | CU 6 | | ✔ | | | | | |
| 10 | `eliminar_materia_plan` | CU 7 (opcional) | | ✔ | | | | | |
| 11 | `crear_correlativa` | CU 8 | | ✔ | | | | | |
| 12 | `listar_comision` | P1 Gestión de Comisiones (17 y CU4) | | ✔ | ✔ | | ✔ | | |
| 13 | `crear_comision` | CU 17 | | ✔ | | | | | |
| 14 | `listar_alumno` | P1 Gestión de Alumnos (CU2, CU3, cambio, CU5, CU7) | | | ✔ | ✔ | | ✔ | ✔ (Policy: solo sus filas) |
| 15 | `crear_preinscripcion` | CU1 ("Registrar Alumno" / "Nuevo Registro") | | | ✔ | ✔ | | | |
| 16 | `crear_usuario_alumno` | CU 9 (P2–P3 del CU1) | | | ✔ | ✔ (ver §7-21) | | | |
| 17 | `crear_inscripcion` | CU2 (botón "Inscribir") | | | ✔ | ✔ | | | |
| 18 | `crear_inscripcion_comision` | CU3 (ícono "C", P2, "Nueva Inscripción") | | | ✔ | | | | |
| 19 | `editar_inscripcion_comision` | CU 19 Cambio de Comisión | | | ✔ | | | | |
| 20 | `crear_regularidad` | CU4 (P2–P5) | | | ✔ | | ✔ | | |
| 21 | `listar_acta_examen` | P1 Gestión de Actas Examen (21, 22, CU6) | | ✔ | ✔ | | | | |
| 22 | `crear_acta_examen` | CU 21 | | ✔ | | | | | |
| 23 | `editar_acta_examen` | CU 22 | | ✔ | | | | | |
| 24 | `crear_inscripcion_examen` | CU5 (ícono "Examen", "Inscribir") | | | ✔ | | | | ✔ (Policy) |
| 25 | `exportar_inscripcion_examen` | CU5 comprobante ("Compr.") | | | ✔ | | | | ✔ (Policy) |
| 26 | `crear_asentamiento_acta` | CU6 (P2–P4) | | | ✔ | | | | |
| 27 | `listar_cuota` | P1 Gestión de Cuotas (33, 34) | ✔ | ✔ | | | | | |
| 28 | `crear_cuota` | CU 33 | ✔ | ✔ | | | | | |
| 29 | `editar_cuota` | CU 34 | ✔ | ✔ | | | | | |
| 30 | `listar_caja` | P1 Gestión de Cajas (35, 36) | | | | | | ✔ | |
| 31 | `crear_caja` | CU 35 Apertura | | | | | | ✔ | |
| 32 | `editar_caja` | CU 36 Cierre | | | | | | ✔ | |
| 33 | `crear_pago` | CU7 (ícono "$", P2) | | | | | | ✔ | |
| 34 | `exportar_pago` | CU7 comprobante PDF | | | | | | ✔ | |
| 35 | `listar_personal` | P1 Gestión de Personal | ✔ | | | | | | |
| 36 | `crear_personal` | CU 25 | ✔ | | | | | | |
| 37 | `editar_personal` | CU 26 | ✔ | | | | | | |
| 38 | `eliminar_personal` | CU 27 | ✔ | | | | | | |
| 39–42 | `listar_usuario`, `crear_usuario`, `editar_usuario`, `eliminar_usuario` | Base común: Usuarios | (SUPERADMIN) | | | | | | |
| 43–46 | `listar_rol`, `crear_rol`, `editar_rol`, `eliminar_rol` | Base común: Roles y permisos | (SUPERADMIN) | | | | | | |
| 47–50 | `listar_catalogo`, `crear_catalogo`, `editar_catalogo`, `eliminar_catalogo` | ABM genérico de catálogos | (SUPERADMIN) | | | | | | |
| 51–52 | `listar_auditoria`, `ver_auditoria` | Módulo Auditoría | (SUPERADMIN) | | | | | | |

Notas: los permisos 39–52 no se asignan a ningún rol de la tesis (SUPERADMIN los tiene por `Gate::before`); quedan creados para poder asignarlos desde la pantalla de Roles si Nicolás quiere (p. ej. Auditoría al DG). Cada pantalla compartida muestra cada botón/ícono solo si el usuario tiene su permiso (`@can`), y la ruta lo exige igual (§1).

### 2.3 Menú lateral (`config/adminlte.php`, `'can'`)

| Grupo (header) | Ítem | Ruta | `can` |
|---|---|---|---|
| Académico | Carreras | `panel.carreras.index` | `listar_carrera` |
| | Materias | `panel.materias.index` | `listar_materia` |
| | Planes de Estudio | `panel.planes.index` | `listar_plan_estudio` |
| | Comisiones (incluye Regularidad CU4) | `panel.comisiones.index` | `listar_comision` |
| Alumnos | Preinscripción | `panel.preinscripcion.create` | `crear_preinscripcion` |
| | Gestión de Alumnos (CU2, CU3, cambio, CU5, CU7) | `panel.alumnos.index` | `listar_alumno` |
| Exámenes | Actas de Examen (21, 22, CU6) | `panel.actas.index` | `listar_acta_examen` |
| Finanzas | Cuotas por Carrera | `panel.cuotas.index` | `listar_cuota` |
| | Cajas | `panel.cajas.index` | `listar_caja` |
| Personal | Gestión de Personal | `panel.personal.index` | `listar_personal` |
| Administración | Usuarios | `panel.usuarios.index` | `listar_usuario` |
| | Roles y Permisos | `panel.roles.index` | `listar_rol` |
| | Catálogos | `panel.catalogos.index` | `listar_catalogo` |
| | Auditoría | `panel.auditoria.index` | `listar_auditoria` |

Un ítem por pantalla de entrada: varios CU (CU2, CU3, CU5, CU7) arrancan, según sus diagramas de estados, en la misma P1 "Gestión de Alumnos" (Fig. 72 p. 97); CU4 arranca en "Gestión de Comisiones"; CU6 en "Gestión de Actas Examen". Por eso no se crea un ítem por CU sino uno por pantalla de inicio (diferencia con §2 "un ítem por CU", anotada en §4).

---

## 3. Modelo de datos global

### 3.1 Convenciones

- Fuente principal: diagrama de 4 hojas corregido, cruzado con análisis/diseño de cada CU.
- Tablas en plural snake_case; **PK `id`** (bigint autoincrement) en lugar de `idXxx`; FK `{entidad}_id` con `constrained()->restrictOnDelete()` e índice.
- Atributos de la tesis en snake_case literal (`nombreCarrera` → `nombre_carrera`). Excepción: `users` usa los nombres de Laravel/Fortify para correo y contraseña.
- Estados y valores de §3: columnas `string` + cast a Enum PHP (no `ENUM` de MySQL, para migraciones reversibles y portables).
- Multiplicidad 1..1 → `NOT NULL`; 0..1 → `nullable`; 1..* se valida en el Service.
- Fechas `date`; donde el wireframe muestra hora, `dateTime`. Montos `decimal(12,2)`; notas `decimal(4,2)`.
- `timestamps()` en todas las tablas de dominio. **SoftDeletes solo en tablas de catálogo** (para el ABM genérico del SUPERADMIN). Las bajas de la tesis se hacen por estado (cuenta "Inactivo", DetallePlanEstudio "Inactivo", AlumnoComision "Caducado", IntegranteTribunal "Inactivo"), porque la especificación las define así (ver §4).
- Cada modelo de dominio usa el trait común de auditoría (`LogsActivity`, `logOnlyDirty`).

### 3.2 `users` (Usuario) + Spatie (Rol)

`users` (se modifica la migración `0001_01_01_000000_create_users_table.php`; `localidad_id` se agrega en una migración posterior porque `localidades` se crea después):

| Columna | Tipo | Null | Atributo tesis | Notas |
|---|---|---|---|---|
| id | bigint PK | no | idUsuario | |
| dni_usuario | string(15) | no | dniUsuario | **unique**; no se usa para entrar |
| apellido_usuario | string(100) | no | apellidoUsuario | |
| nombre_usuario | string(100) | no | nombreUsuario | |
| fecha_nacimiento | date | sí | fechaNacimiento | |
| sexo | string(20) | sí | sexo | valores: ver §7-12 |
| estado_civil | string(30) | sí | estadoCivil | valores: ver §7-12 |
| telefono_usuario | string(30) | sí | telefonoUsuario | |
| celular_usuario | string(30) | sí | celularUsuario | |
| email | string(150) | no | correoUsuario | **unique**; login (Fortify `username`/`email`) |
| email_verified_at | timestamp | sí | — | técnico (emailVerification) |
| direccion_usuario | string(200) | sí | direccionUsuario | |
| barrio_usuario | string(100) | sí | barrioUsuario | |
| pais_usuario | string(80) | sí | paisUsuario | |
| localidad_id | FK localidades | no | Usuario 1..* "nació en/reside en" 1..1 Localidad | ver §7-16 |
| foto_usuario | string(255) | sí | fotoUsuario | ruta en disco `public` |
| password | string | **sí** | contrasenia | nulo hasta que el usuario la define (link firmado) |
| fecha_alta | date | no | fechaAlta | |
| estado_cuenta | string(20) | no | estadoCuenta | Enum `EstadoCuenta`: `Activo` / `Inactivo`; índice |
| remember_token, timestamps | | | | técnico |

Login: Fortify con `username = email`. `authenticateUsing`: busca por `email` con `estado_cuenta = Activo` → si tiene rol `Alumno`, exige al menos un `alumnos` con `estado_legajo = Activo` (si no: "Este usuario no está activo") → `Hash::check`.

Spatie (`php artisan vendor:publish` de laravel-permission): `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`. Migración extra `add_descripcion_estado_to_roles`: `descripcion` string(255) null (descripcionRol, se muestra en P2 de Personal), `estado` string(20) default `Activo` (estadoRol, Enum `EstadoGeneral`). `nombreRol` = `roles.name`. idRol = `roles.id`.

### 3.3 Catálogos (solo seeder + ABM genérico; SoftDeletes)

| Tabla | Columnas | Índices |
|---|---|---|
| provincias | id, provincia string(100) | unique(provincia) |
| localidades | id, provincia_id FK NOT NULL, localidad string(100) | unique(provincia_id, localidad) |
| modalidades | id, modalidad string(50) | unique |
| duraciones | id, duracion string(50), **cantidad_anios** tinyint unsigned (nuevo, §4) | unique(duracion) |
| regimenes | id, regimen string(50) | unique |
| cuatrimestres | id, cuatrimestre string(50) | unique |
| llamados | id, llamado string(50) | unique |
| tipos_acta | id, descripcion string(50) | unique |
| roles_tribunal | id, descripcion string(50) | unique |
| tipos_documento | id, descripcion string(80) | unique |
| motivos | id, descripcion string(150), accion string(50) | index(accion) |
| medios_pago | id, medio_pago string(50) | unique |
| tipos_gestion_titulo | id, descripcion string(80) | unique |
| meses | id (= orden), descripcion string(30) | unique. Sin SoftDeletes ni ABM: valores fijos (1 Inscripción, 2 Marzo … 11 Diciembre), seeder |

Valores iniciales propuestos en §5.6 (varios faltan en la tesis: §7-12).

### 3.4 Usuarios complementarios

| Tabla | Columnas (tipo, null) | Claves / índices |
|---|---|---|
| encuestas_socioeconomicas | id; user_id FK NOT NULL; tiene_hijos, vive_solo, padres_viven, madre_trabaja, padre_trabaja, trabaja, recibe_ayuda_economica, estudios_superiores_previos, posee_discapacidad, miembro_poblacion_indigena, hablante_lenguas_indigenas, sabe_usar_pc, es_usuario_internet: boolean default false; nivel_estudios_madre string(50) null; nivel_estudios_padre string(50) null; idiomas string(255) null | unique(user_id) (Usuario 1..1 — 0..1 Encuesta) |
| novedades | id; user_id FK NOT NULL; alumno_id FK null; motivo_id FK NOT NULL; fecha_novedad date; observacion_novedad text null | index(user_id), index(motivo_id) |

### 3.5 Académico

| Tabla | Columnas | Claves / índices |
|---|---|---|
| carreras | id; nombre_carrera string(150); resolucion_carrera string(50); estado_carrera string(20) (Enum `EstadoCarrera`: `Pendiente`, `Activo`, `Inactivo`); modalidad_id FK NOT NULL; duracion_id FK NOT NULL | unique(nombre_carrera); index(estado_carrera) |
| materias | id; nombre_materia string(150); estado_materia string(20) (`EstadoGeneral`) | unique(nombre_materia) |
| planes_estudio | id; carrera_id FK NOT NULL; anio_plan_estudio smallint unsigned; estado_plan_estudio string(20) (`EstadoGeneral`) | unique(carrera_id, anio_plan_estudio); index(carrera_id, estado_plan_estudio). Un solo plan `Activo` por carrera (Service) |
| detalles_plan_estudio | id; plan_estudio_id FK; materia_id FK; regimen_id FK; cuatrimestre_id FK (todos NOT NULL); codigo_det_plan_estudio string(20); carga_horaria_det_plan_estudio smallint unsigned; anio_curso_det_plan_estudio tinyint unsigned; estado_det_plan_estudio string(20) (`EstadoGeneral`); fecha_baja date null | unique(plan_estudio_id, materia_id); unique(plan_estudio_id, codigo_det_plan_estudio); index(plan_estudio_id, anio_curso_det_plan_estudio) |
| correlativas | id; detalle_plan_estudio_id FK NOT NULL (materia que exige); detalle_plan_correlativa_id FK → detalles_plan_estudio NOT NULL (idDetPlanCorrelativa); p_cursar string(20) null; p_rendir string(20) null (Enum `CondicionCorrelativa`: `Regular`, `Aprobada`) | unique(detalle_plan_estudio_id, detalle_plan_correlativa_id) |
| comisiones | id; carrera_id FK; modalidad_id FK; codigo_comision string(20); capacidad_comision smallint unsigned; carga_horaria_comision smallint unsigned; horario_comision string(100); anio_curso tinyint unsigned; anio_lectivo smallint unsigned; estado_comision string(20) (`EstadoGeneral`) | unique(carrera_id, codigo_comision, anio_curso, anio_lectivo) (mensaje del CU 17); index(carrera_id, anio_lectivo, estado_comision) |
| det_comisiones | id; comision_id FK; detalle_plan_estudio_id FK; estado_det_comision string(20) (`EstadoGeneral`) | unique(comision_id, detalle_plan_estudio_id) |

### 3.6 Alumnos

| Tabla | Columnas | Claves / índices |
|---|---|---|
| alumnos | id; user_id FK NOT NULL; carrera_id FK NOT NULL; plan_estudio_id FK NOT NULL (Alumno 1..* "cursa" 1..1 PlanEstudio = plan activo al preinscribir); comision_actual_id FK → comisiones **null** ("cursa actualmente en" 0..1); nro_legajo int unsigned null (se genera en CU2); estado_alumno string(30) (`EstadoAlumno`); estado_legajo string(20) null (`EstadoLegajo`); situacion_estudios string(20) (`SituacionEstudios`); adeuda_materia boolean default false; observaciones text null | unique(user_id, carrera_id) (legajo por carrera); unique(nro_legajo); index(estado_alumno, estado_legajo) |
| documentos_presentados | id; user_id FK NOT NULL; tipo_documento_id FK NOT NULL; nombre_documento string(150); fecha_presentacion date; archivo_documento string(255) (ruta disco privado); estado_documento string(20) (`EstadoGeneral`, ver §7-12) | index(user_id, tipo_documento_id) |
| alumno_comision | id; alumno_id FK; det_comision_id FK; fecha_inscripcion date; estado_alumno_comision string(20) (`EstadoAlumnoComision`); tipo_inscripcion string(20) (`TipoInscripcion`); condicion_final string(20) null (`CondicionCursada`; null = se muestra "En Curso"); nota_p1, nota_p2, nota_r1, nota_r2 decimal(4,2) null; faltas_alumno smallint unsigned null | index(alumno_id, estado_alumno_comision); index(det_comision_id, estado_alumno_comision) |
| estados_curriculares | id (nuevo); alumno_id FK; detalle_plan_estudio_id FK; condicion string(20) (`CondicionCurricular`); fecha_condicion date; nota_condicion decimal(4,2) null; nro_acta int unsigned null | index(alumno_id, detalle_plan_estudio_id, fecha_condicion) |

### 3.7 Exámenes y título

| Tabla | Columnas | Claves / índices |
|---|---|---|
| tribunales | id; timestamps | — |
| actas_examen | id; nro_acta int unsigned (generarNroActa); detalle_plan_estudio_id FK; modalidad_id FK; llamado_id FK; tipo_acta_id FK; tribunal_id FK NOT NULL; fecha_acta date; estado_acta string(20) (`EstadoActa`); asentada char(1) default `N` (`Asentada`: `S`/`N`); nro_folio int unsigned null | unique(nro_acta); unique(tribunal_id); index(detalle_plan_estudio_id, estado_acta) |
| integrantes_tribunal | id; tribunal_id FK; user_id FK (Docente); rol_tribunal_id FK; estado_integrante string(20) (`EstadoGeneral`: `Activo`/`Inactivo`) | index(tribunal_id, estado_integrante) |
| detalles_acta | id; acta_examen_id FK; alumno_id FK; nota_acta decimal(4,2) null; observacion string(255) null; estado_detalle_acta string(20) (`EstadoDetalleActa`); condicion_final string(20) null (`CondicionExamen`); uuid char(36) (QR del comprobante) | unique(acta_examen_id, alumno_id); unique(uuid) |
| titulos_tramite | id; alumno_id FK; tipo_gestion_titulo_id FK **null**; fecha_creacion date; fecha_inicio, fecha_recepcion, fecha_retiro date null; estado_tramite string(20) (`EstadoTramite`); observacion text null | unique(alumno_id) (Alumno 1..1 — 0..1) |

### 3.8 Finanzas y becas

| Tabla | Columnas | Claves / índices |
|---|---|---|
| cuotas | id; carrera_id FK; mes_id FK → meses NOT NULL (Cuota 0..* — 1..1 Mes); monto decimal(12,2); anio smallint unsigned; estado_cuota string(20) (`EstadoGeneral`). Sin `mes` ni `descripcion` (decisión §7-7) | index(carrera_id, anio, mes_id, estado_cuota); unicidad de "Activo" por (carrera, año, Mes) en el Service del CU 33/34 |
| tipos_beca | id; nombre_beca string(100); tipo_descuento string(20); valor_descuento decimal(12,2); requisito_academico text null; requisito_economico text null; estado_beca string(20) | sin pantallas (§7-8) |
| convocatorias_beca | id; tipo_beca_id FK; anio_lectivo smallint; fecha_desde, fecha_hasta date; cantidad_disponible smallint unsigned; estado_convocatoria string(20) | sin pantallas |
| solicitudes_beca | id; alumno_id FK; convocatoria_beca_id FK; situacion_economica text; motivo_solicitud text; fecha_solicitud date; estado_solicitud string(20); motivo_rechazo text null | sin pantallas |
| becas | id; alumno_id FK; convocatoria_beca_id FK; solicitud_beca_id FK unique; fecha_inicio, fecha_fin date; valor_beca decimal(12,2); estado_beca string(20) (`EstadoGeneral`); motivo_baja text null | index(alumno_id, estado_beca) |
| cajas | id; user_id FK (Cajero) NOT NULL; fecha_apertura dateTime; monto_inicial decimal(12,2); fecha_cierre dateTime null; monto_final decimal(12,2) null; diferencia_total decimal(12,2) null; observacion_cierre text null; estado_caja string(20) (`EstadoCaja`) | index(user_id, estado_caja) |
| detalles_cierre_caja | id; caja_id FK; medio_pago_id FK; monto_esperado, monto_contado, diferencia decimal(12,2) | unique(caja_id, medio_pago_id) |
| pagos | id; alumno_id FK; caja_id FK; medio_pago_id FK NOT NULL (un solo medio por pago, decisión §7-5); fecha_pago dateTime; monto_total decimal(12,2); estado_pago string(20) (`EstadoGeneral`) | index(alumno_id, estado_pago); index(caja_id, estado_pago); index(caja_id, medio_pago_id) |
| detalles_pago_cuota | id; pago_id FK; cuota_id FK; beca_id FK null; monto_aplicado decimal(12,2); monto_bonificado decimal(12,2) default 0 | index(cuota_id); index(pago_id) |
| facturas_comprobante | id; pago_id FK; numero_comprobante int unsigned; fecha_emision date; estado_factura string(20) (`EstadoGeneral`); uuid char(36) | unique(pago_id) (1..1); unique(numero_comprobante); unique(uuid) |

Tablas técnicas: `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` (esqueleto) y `activity_log` (spatie/laravel-activitylog).
**No se crean** (fuera de alcance, sin UI ni dependencia de los 7 CU): `solicitudes_equivalencia`, `detalles_solicitud_equivalencia`, `dictamenes_equivalencia`, `tipos_solicitud_equivalencia`. Por eso `documentos_presentados` no tiene FK a solicitudes de equivalencia ni de beca.

Totales: **43 tablas de dominio** (13 catálogos + `meses` + 25 de dominio + 4 de becas), más 5 de Spatie Permission, 1 de Activitylog y 7 técnicas de Laravel.

### 3.9 Enums PHP (`app/Enums`, con `label()` y `badge()` para `<x-badge-estado>`)

| Enum | Valores exactos | Usado en |
|---|---|---|
| `EstadoGeneral` | `Activo`, `Inactivo` | materias, planes, detalles plan, comisiones, det_comisiones, cuotas, pagos, facturas, integrantes, becas, documentos, roles |
| `EstadoCuenta` | `Activo`, `Inactivo` | users.estado_cuenta |
| `EstadoCarrera` | `Pendiente`, `Activo`, `Inactivo` | carreras |
| `EstadoAlumno` | `Preinscripto`, `Inscripto`, `Título en Trámite`, `Título Entregado` | alumnos.estado_alumno |
| `EstadoLegajo` | `Activo`, `Cerrado` | alumnos.estado_legajo |
| `SituacionEstudios` | `Completo`, `En curso`, `Incompleto` | alumnos |
| `EstadoAlumnoComision` | `Activo`, `Caducado` | alumno_comision |
| `TipoInscripcion` | `Por Comisión` | alumno_comision |
| `CondicionCursada` | `Regular`, `Libre` (+ etiqueta de solo lectura `En Curso` cuando es null) | alumno_comision.condicion_final |
| `CondicionCurricular` | `Regular`, `Libre`, `Aprobado`, `Desaprobado` | estados_curriculares |
| `CondicionExamen` | `Aprobado`, `Desaprobado` (nota ≥ 4) | detalles_acta.condicion_final |
| `CondicionCorrelativa` | `Regular`, `Aprobada` | correlativas |
| `EstadoActa` | `Pendiente`, `Finalizado` | actas_examen |
| `Asentada` | `S`, `N` | actas_examen |
| `EstadoDetalleActa` | `Activo` | detalles_acta |
| `EstadoTramite` | `Sin Gestión`, `Iniciado`, `En Instituto`, `Retirado` | titulos_tramite |
| `EstadoCaja` | `Abierta`, `Cerrada` | cajas |
| `RolSistema` | ver §2.1 | roles |

### 3.10 Orden de migraciones

1. `0001_01_01_000000_create_users_table` (modificada: columnas de Usuario sin `localidad_id`), `…000001_create_cache_table`, `…000002_create_jobs_table`.
2. `create_permission_tables` (Spatie) y `add_descripcion_estado_to_roles`.
3. `create_activity_log_table` + sus 2 migraciones de columnas (Activitylog 4.x).
4. `create_provincias_table`, `create_localidades_table`, `add_localidad_id_to_users_table`.
5. Catálogos: modalidades, duraciones, regimenes, cuatrimestres, llamados, tipos_acta, roles_tribunal, tipos_documento, motivos, medios_pago, tipos_gestion_titulo; `meses`.
6. `encuestas_socioeconomicas`.
7. `carreras` → `materias` → `planes_estudio` → `detalles_plan_estudio` → `correlativas` → `comisiones` → `det_comisiones`.
8. `alumnos` → `documentos_presentados` → `novedades` → `alumno_comision` → `estados_curriculares`.
9. `tribunales` → `actas_examen` → `integrantes_tribunal` → `detalles_acta` → `titulos_tramite`.
10. `cuotas` → `tipos_beca` → `convocatorias_beca` → `solicitudes_beca` → `becas` → `cajas` → `detalles_cierre_caja` → `pagos` → `detalles_pago_cuota` → `facturas_comprobante`.

Todas con `down()` simétrico; `migrate:fresh --seed` y `migrate:rollback` deben pasar en limpio.

---

## 4. Diferencias propuestas respecto de los diagramas (para `docs/diferencias-tesis.md`)

| CU | Diagrama | Qué dice | Qué se propone | Por qué |
|---|---|---|---|---|
| General | 4 hojas y todos | PK `idXxx` Integer | PK `id`, FK `{entidad}_id` | Convención Eloquent; trazabilidad en `docs/trazabilidad.md` |
| General | 4 hojas | Estados como `String` | `string` + Enum PHP con los valores de §3 | §1b/§3; evita valores libres |
| General | Colaboraciones Materia, Comisión, Personal, Apertura de Caja | `"Activa"`, `"Inactiva"`, `estadoCaja='Abierto'` | `Activo`/`Inactivo`; Caja `Abierta`/`Cerrada` | §3 (estado activo siempre masculino; excepción Caja). Ver §7-11 para los textos de los wireframes |
| Base | 4 hojas H1 | Usuario.correoUsuario, contrasenia | `users.email`, `users.password` (nullable) + `email_verified_at`, `remember_token` | Fortify; login por correo (§1, §2) |
| CU 25 | Especificación p. 143 | "cuenta de acceso (DNI y Contraseña)" | Login con correo y contraseña; DNI único pero no se usa para entrar | §1/§2 de la skill |
| Base | 4 hojas H1 | Clase Rol (idRol, nombreRol, descripcionRol, estadoRol) | Tabla `roles` de Spatie + columnas `descripcion`, `estado` | §2 de la skill |
| Base | 4 hojas H1 | Subclases Docente, Cajero, Coordinador, Preceptor, AuxiliarAdministrativo, DirectorGeneral | Sin tablas; rol Spatie + scopes (`User::docentes()`, `User::cajeros()`) | No tienen atributos propios |
| CU 9 / CU1 | `usuario_alumno/clases_analisis` | provincia, localidad como String; idiomas String | `localidad_id` FK (provincia por la localidad); `idiomas` string(255) | 4 hojas exige FK a Localidad; wireframe P3 es multiselección de idiomas |
| CU 9 | 4 hojas H1 | `idiomas: Integer` | `idiomas` string(255) (lista) | El wireframe pide "Idiomas que habla" (Español, Guaraní…) |
| Soporte 3/4 | `carrera/clases_analisis_modificar` | modalidadCarrera, duracionCarrera como atributos | FK `modalidad_id`, `duracion_id` | 4 hojas y diseño |
| Soporte 17, 6 | 4 hojas H2 | Duracion solo `duracion: String` | + `cantidad_anios` tinyint | CU 17 valida "Año de Curso entre 1 y la duración de la Carrera"; CU 6 muestra "Años de Duración" |
| Soporte 7 | Análisis/diseño baja materia del plan | `DetallePlanEstudio.fechaBaja` (no está en 4 hojas) | Se agrega `fecha_baja` null | La colaboración la carga |
| CU2 | `CU2/clases_analisis`, `clases_diseno` | Alumno.presentaTitulo, tituloSecundario | No se agregan (pendiente §7-17) | No están en 4 hojas; se deriva de DocumentoPresentado |
| CU1–CU7 | 4 hojas H2 | Alumno "cursa" PlanEstudio | `alumnos.plan_estudio_id` NOT NULL = plan activo al preinscribir | Multiplicidad 1..1 |
| CU3 | 4 hojas H2 | Alumno "cursa actualmente en" Comision 0..1 | `alumnos.comision_actual_id` null | §3 |
| CU4/CU6 | 4 hojas H2 | EstadoCurricular sin identificador | Se agrega `id` | Requisito de Eloquent |
| CU6 | 4 hojas H2 | EstadoCurricular.nroActa Integer | Se mantiene `nro_acta` (sin FK) | Fiel al diagrama |
| Soporte 8 | 4 hojas H2 | Correlativa con dos asociaciones a DetallePlanEstudio | `detalle_plan_estudio_id` y `detalle_plan_correlativa_id`; `p_cursar`/`p_rendir` nullables | Una fila por correlativa con condición para cursar y/o rendir |
| Soporte 8 | Especificación p. 121 | "Registrar correlatividades … en estado Activo" | No se agrega estado | Correlativa no tiene atributo de estado (§7-20) |
| CU7 | Wireframe P2 | Combo medio + monto + "Agregar" sugiere varios medios | Se respeta la tesis: Pago 1..1 MedioPago. "Agregar" fija el único medio del pago (reemplaza el anterior); "Importe en <medio>" muestra lo agregado; "Saldo Pendiente" = Total a Pagar − importe; "Registrar Pago" se habilita con saldo 0 | Decisión de Nicolás §7-5 (2026-10-25): un solo medio de pago |
| CU7 / CU5 | 4 hojas | FacturaComprobante y DetalleActa sin identificador público | + `uuid` | QR a ruta pública de validación (§1) |
| CU 35/36 | 4 hojas H4 | fechaApertura/fechaCierre `Date` | `dateTime` | El wireframe de cierre muestra hora (15/12/2024 18:38:34) |
| CU 36 | 4 hojas H4 | Caja.montoFinal | Se completa al cerrar = suma de montos contados | La colaboración de cierre no lo carga |
| CU6 | 4 hojas H2 | TituloTramite.nroLegajo; TituloTramite 1..1 TipoGestionTitulo | Sin `nro_legajo` (se obtiene del alumno); `tipo_gestion_titulo_id` null | Redundante; CU6 no informa el tipo de gestión |
| CU6 | Análisis CU6 | ActaExamen 1..* DetallePlanEstudio/Llamado/TipoActa | Se usa 0..* (4 hojas) | 4 hojas es la versión corregida |
| Soporte 21 | 4 hojas H3 | Tribunal sin atributos, 1..1 con Acta | Se mantiene tabla `tribunales` | Fiel al diagrama (alternativa: FK directa acta→integrantes) |
| Soporte 21 | Análisis acta | ActaExamen 1..1 — 1..* Tribunal | 1..1 (4 hojas) | Versión corregida |
| CU1–CU5 | 4 hojas H1/H2 | Novedad asociada a Usuario y a Alumno | `novedades.user_id` NOT NULL, `alumno_id` null | Dos asociaciones; la de Alumno es de CU fuera de alcance |
| Equivalencias/Becas | 4 hojas H3/H4 | Clases de equivalencias y becas | Equivalencias no se crean; Becas se crean sin pantallas | Fuera de alcance; CU7 necesita Beca activa |
| Base | — | Bajas de entidades principales | SoftDeletes solo en catálogos; bajas de la tesis por estado | La especificación define estados de baja explícitos |
| Menú | Skill §2 | Un ítem de menú por CU | Un ítem por pantalla de entrada | Los diagramas de estados hacen que CU2, CU3, CU5, CU7 empiecen en la misma P1 |
| CU 9 | Lista p. 58 | Actor PR | Permiso a PR y AA | CU1 (AA–PR) lo invoca en su escenario alternativo 4.3 |
| CU 33/34 | Especificación | Actor solo DG (colab 34: DG) | Permisos a DG y CC | Lista de CU p. 59: CC – DG |
| Base | Skill §1 | simplesoftwareio/simple-qrcode | bacon/bacon-qr-code 3.1.1 directo (QrService) | Conflicto de dependencias con Fortify (§5.1, §7-2) |

---

## 5. Base común (§7 pasos 1–6)

### 5.1 Paquetes (versiones exactas, verificadas con `composer show -a` y `composer require --dry-run` contra PHP 8.2.4 y Laravel 12.70.1; composer.json/lock quedaron sin cambios)

Producción (`require`):

| Paquete | Versión | Motivo de la versión |
|---|---|---|
| php | ^8.2 | entorno |
| laravel/framework | 12.70.1 | instalado |
| laravel/tinker | 2.11.1 | instalado |
| laravel/fortify | 1.41.0 | última; trae bacon/bacon-qr-code 3.1.1 y laravel/passkeys 0.2.1 |
| spatie/laravel-permission | 6.25.0 | 7.x y 8.x exigen PHP ^8.3 |
| jeroennoten/laravel-adminlte | 3.16.0 | 3.x = AdminLTE 3.2 (Bootstrap 4 + jQuery); 4.x usa AdminLTE 4 (Bootstrap 5), no sirve |
| yajra/laravel-datatables-oracle | 12.7.2 | 13.x exige Laravel 13 y PHP 8.3 |
| barryvdh/laravel-dompdf | 3.1.2 | última (dompdf 3.1.6) |
| maatwebsite/excel | 3.1.70 | 4.x exige PHP 8.3 |
| spatie/laravel-activitylog | 4.12.3 | 5.x exige PHP 8.4 |
| guzzlehttp/guzzle | 7.16.0 | ya está en el lock; 8.x innecesario |
| ~~simplesoftwareio/simple-qrcode 4.2.0~~ | **no instalable** | exige bacon ^2.0 y Fortify ^3.0 (conflicto verificado). Usar bacon/bacon-qr-code 3.1.1 (transitivo de Fortify; declararlo explícito `3.1.1`) |

Desarrollo (`require-dev`): laravel/pint 1.30.4, phpunit/phpunit 11.5.57, spatie/laravel-ignition 2.12.1, fakerphp/faker 1.24.1, mockery/mockery 1.6.15, nunomaduro/collision 8.9.5, laravel/pail 1.2.7; quitar laravel/sail. Opcional: larastan/larastan 3.13.2 (§7-33). Todas las constraints actuales `^x` se reemplazan por la versión exacta del lock.

npm (`package.json`): quitar `tailwindcss` y `@tailwindcss/vite`; fijar `vite` 7.3.7, `laravel-vite-plugin` 2.1.0, `axios` 1.20.0, `concurrently` (versión exacta al instalar). Bootstrap/jQuery/plugins vienen de AdminLTE (`php artisan adminlte:install` y `php artisan adminlte:plugins install` para DataTables + Responsive, Select2, SweetAlert2, TempusDominus/moment, bs-custom-file-input; confirmar nombres con `php artisan adminlte:plugins`).

Extensiones PHP verificadas: gd, zip, intl, mbstring, fileinfo, xml (necesarias para dompdf/excel).

### 5.2 `.env.example`

```
APP_NAME="Sistema LAS"
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_AR
APP_TIMEZONE=America/Argentina/Buenos_Aires
APP_URL=http://localhost/Tesis/Tesis/sistema-las/public   # o http://sistema-las.test
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_las
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=database
CACHE_STORE=file
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-responder@institutomilagro.edu.ar"
MAIL_FROM_NAME="${APP_NAME}"
ADMIN_NOMBRE=Administrador
ADMIN_APELLIDO=Sistema
ADMIN_DNI=00000000
ADMIN_EMAIL=admin@las.test
ADMIN_PASSWORD=
LAS_INSTITUTO_NOMBRE="Instituto Superior del Milagro"
LAS_PUNTO_VENTA=0001
LAS_CUOTA_DIA_VENCIMIENTO=10
LAS_LINK_ALTA_HORAS=48
LAS_LEGAJO_INICIAL=10000
```
Se quitan REDIS_*, AWS_*, MEMCACHED_*. Los `LAS_*` se leen desde un `config/las.php` (sin valores mágicos). Verificar que `config/app.php` use `env('APP_TIMEZONE')`; si tiene `'UTC'` fijo, cambiarlo.

### 5.3 `bootstrap/app.php` y providers

- `withRouting(web:, commands:, health: '/up', then: fn () => Route::middleware(['web','auth','verified'])->prefix('panel')->name('panel.')->group(base_path('routes/panel.php')))`.
- `withMiddleware`: alias `role`, `permission`, `role_or_permission` (Spatie `\Spatie\Permission\Middleware\*`); `redirectGuestsTo('/login')`; `redirectUsersTo('/panel')`.
- `withExceptions`: vista 403/404/419 en español con layout AdminLTE.
- `bootstrap/providers.php`: `AppServiceProvider`, `FortifyServiceProvider`.
- `AppServiceProvider::boot`: `Gate::before(fn ($u) => $u->hasRole(RolSistema::SuperAdmin->value) ? true : null)`; `Model::preventLazyLoading(! app()->isProduction())`; `Carbon::setLocale('es')`; registro de Policies (auto-discovery).
- `routes/web.php`: `/` → redirect login; ruta de definir contraseña firmada (`signed`, un solo uso); `validar/inscripcion-examen/{uuid}` y `validar/comprobante/{uuid}` (públicas, QR).
- `routes/panel.php`: grupos por módulo (`academico`, `alumnos`, `examenes`, `finanzas`, `personal`, `administracion`) con `can:` en cada ruta; mutaciones por POST/PUT/DELETE; DataTables por `POST panel/{modulo}/datatable`.

### 5.4 `config/fortify.php` y FortifyServiceProvider

- `'guard' => 'web'`, `'passwords' => 'users'`, `'username' => 'email'`, `'email' => 'email'`, `'lowercase_usernames' => true`, `'home' => '/panel'`, `'views' => true`, `'limiters' => ['login' => 'login']`.
- `'features' => [Features::resetPasswords(), Features::emailVerification()]` (sin registro, sin 2FA, sin passkeys).
- Provider: `Fortify::loginView`, `requestPasswordResetLinkView`, `resetPasswordView`, `verifyEmailView` → vistas Blade que extienden `adminlte::auth.*`; `Fortify::authenticateUsing(...)` (§3.2); `RateLimiter::for('login', fn ($r) => Limit::perMinute(5)->by(Str::lower($r->email).'|'.$r->ip()))`.
- `User` implementa `MustVerifyEmail`; `sendPasswordResetNotification` y `sendEmailVerificationNotification` con notificaciones propias en español que implementan `ShouldQueue`.
- Alta por administrador (Usuarios del SUPERADMIN y usuarios alumno): `URL::temporarySignedRoute('password.definir', now()->addHours(config('las.link_alta_horas')), ['user' => $id, 'hash' => sha1(email)])`; un solo uso = la ruta rechaza si `password` ya no es null. Logout por POST con CSRF (Fortify).

### 5.5 `config/adminlte.php`

`title`/`title_postfix` desde `APP_NAME`; logo con nombre del instituto; `usermenu_enabled` true (perfil y logout); `layout_fixed_sidebar` true, `sidebar_collapse` false, `sidebar_mini` 'lg'; `dashboard_url` 'panel', `logout_url` 'logout', `logout_method` 'POST', `login_url` 'login', `register_url` false, `password_reset_url` 'forgot-password', `password_email_url` 'forgot-password', `profile_url` false; `menu` de §2.3 con `header` por grupo y `can`; `plugins` habilitados en todas las páginas: Datatables (+Responsive), Select2, Sweetalert2, TempusDominusBs4, BsCustomFileInput; `livewire` false. Colores del wireframe (bordó #8B2A32) como variables en `resources/css/app.css` (Vite), sin Tailwind.

Quitar Tailwind: borrar `@tailwindcss/vite` de `vite.config.js`, reemplazar `resources/css/app.css` por estilos propios, borrar `resources/views/welcome.blade.php`.

### 5.6 Componentes y código reutilizable (crear antes de los módulos)

Blade (`resources/views/components`):
- `<x-alert />` (flash único), `<x-header-with-title>` (encabezado bordó con título en caja blanca, como todos los wireframes), `<x-form.input>`, `<x-form.select>` (Select2 opcional), `<x-form.date>` (datepicker es), `<x-form.textarea>`, `<x-form.checkbox>`, `<x-form.file>` (adjuntar PDF: CU1, CU2), `<x-btn-accion>` (ícono + tooltip + `@can`), `<x-modal-confirmar>`, `<x-datatable>`, `<x-badge-estado :estado="Enum">`.
- Específicos repetidos en varios wireframes: `<x-card-datos-alumno>` (Legajo / Apellido y Nombre / Carrera: CU3 P2, CU5 P2, CU7 P2), `<x-pantalla-confirmacion>` (todas las P "Confirmación" con botón Aceptar), `<x-wizard-pasos>` (CU1 pasos 1–5), `<x-select-provincia-localidad>` (Personal, CU1 P2), `<x-tabla-tribunal>` (CU 21/22).
JS (`resources/js`): `datatables-es.js` (idioma, responsive, server-side, POST con CSRF), `confirmar.js` (SweetAlert2 sobre `data-confirm`), `form-submit.js` (deshabilitar + spinner), `select2-init.js`, `mascaras.js` (DNI, montos `$ 1.234,56`), `provincia-localidad.js`.
PHP: trait `AuditaCambios` (LogsActivity), `BaseDataTable` (yajra), Enums §3.9, Rules (`Dni`, `NotaValida`, `MontoPositivo`, `ResolucionMinisterial` según §7-19), Services transversales: `CuotaPendienteService` (CU2, CU3, CU5, CU7), `CorrelatividadService` (CU3, CU5), `NumeradorService` (nro_legajo, nro_acta, numero_comprobante con `lockForUpdate`), `PdfService` (dompdf, fecha en español, QR), `QrService` (bacon 3, SVG en data URI), Mailables con layout común (§4 de la skill, `Mail::queue` + `afterCommit`).

### 5.7 Seeders

- `RolesPermisosSeeder` (idempotente: `Permission::firstOrCreate`, `Role::firstOrCreate` + `syncPermissions`, descripciones de rol, `app(PermissionRegistrar::class)->forgetCachedPermissions()`), matriz de §2.2.
- `AdminSeeder`: usuario SUPERADMIN desde `ADMIN_*`; falla con mensaje claro si `ADMIN_PASSWORD` está vacío.
- `CatalogosSeeder` (`updateOrCreate` por clave natural). Valores propuestos (confirmar en §7-12): Provincias (24 de Argentina) y localidades de Salta + capitales; Modalidad: Presencial, Semipresencial, A distancia; Duración: 2 años, 3 años, 4 años (cantidad_anios); Régimen: Anual, Cuatrimestral; Cuatrimestre: 1er Cuatrimestre, 2do Cuatrimestre, Anual; Llamado: "P · 1° Llamado", "S · 2° Llamado", "E · Extraordinario" (vistos en wireframes); TipoActa: Regular, Libre; RolTribunal: Presidente, Vocal 1, Vocal 2; TipoDocumento: Fotocopia de DNI, Certificado de Título; Motivo (accion "Baja de Personal"): Renuncia, Jubilación, Fin de contrato, Otro; MedioPago: Efectivo, Transferencia, Tarjeta de Débito; TipoGestionTitulo: pendiente.
- `DemoSeeder` (solo `local`): un usuario por rol, carrera Activo con plan, materias, correlativas, comisiones, actas, cuotas, una caja abierta, una beca activa; con factories.
- `DatabaseSeeder` llama a los tres primeros siempre y al demo solo en local.

### 5.8 Tests de la base

- `Auth/LoginTest`: login correcto por email; contraseña errónea; cuenta `Inactivo` rechazada; Alumno sin legajo `Activo` → "Este usuario no está activo"; DNI no sirve para entrar; rate limit al 6.º intento; logout solo POST.
- `Auth/PasswordResetTest` y `Auth/EmailVerificationTest`: notificaciones encoladas y en español.
- `Auth/DefinirPasswordTest`: link firmado válido, vencido (403) y reutilizado (403).
- `Autorizacion/RutasPanelTest` con dataset de todas las rutas `panel.*`: invitado → 302 a login; usuario sin permiso → 403; con permiso → 200; SUPERADMIN → 200.
- `Seeders/RolesPermisosSeederTest`: correr dos veces no duplica; cada rol tiene exactamente los permisos de §2.2.
- `Menu/MenuTest`: cada rol ve solo sus ítems.
- BD de tests: MySQL/MariaDB `sistema_las_test` en `phpunit.xml` (§7-30).

---

## 6. Etapas del desarrollo

Criterio de cierre común a todas: `php artisan test` en verde, `vendor/bin/pint --test` sin cambios, `migrate:fresh --seed` en limpio, `docs/trazabilidad.md` y `docs/diferencias-tesis.md` actualizados, plan del CU con cada fila marcada (archivo:línea) y revisión del Agente Revisor sin hallazgos Alta.

| # | Etapa | Entregable | Criterio de cierre específico |
|---|---|---|---|
| E0 | Base común (§7 pasos 1–6) | composer/package con versiones fijas, `.env.example`, `bootstrap/app.php`, Fortify, AdminLTE (layout, login, menú), componentes y JS de §5.6, Spatie + seeders de roles/permisos/admin, módulo Usuarios y Roles (SUPERADMIN, patrón index), sin Tailwind | Tests de §5.8; login visible a 375/1280 px |
| E1 | Migraciones, Enums, modelos, catálogos, factories (§7 paso 7) | 43 tablas de §3, Enums §3.9, modelos con `$table`/`$fillable`/casts/relaciones, `CatalogosSeeder`, `DemoSeeder`, ABM genérico de catálogos (SUPERADMIN), `docs/diferencias-tesis.md` inicial con §4 | Test de relaciones por modelo; rollback completo |
| E2 | Personal (25, 26, 27 opcional) | P1 con pestañas Activos/Eliminados, P2 registrar/modificar, modal baja | Escenarios alternativos con mensajes exactos |
| E3 | Usuarios Alumno (9) | `UsuarioAlumnoService` + FormRequest (sin pantalla propia; la usan P2–P3 de CU1) | Tests de servicio |
| E4 | Carrera (3, 4) | P1, P2 registrar/modificar | Estado inicial `Pendiente`; bloqueo con plan activo |
| E5 | Materia (1, 2) | P1, P2 | Bloqueo si vinculada a plan activo |
| E6 | Plan de Estudio (5) | P1c, P2 | Un plan activo por carrera |
| E7 | Materia al Plan (6) y Baja (7, opcional) | P1, P2, modal baja | Validaciones de código y carga horaria |
| E8 | Correlatividades (8) | P2 | Solo materias de años anteriores |
| E9 | Comisión (17) | P1, P2 (imágenes del PDF p. 232) | Carga horaria calculada; DetComision por materia del año |
| E10 | Cuotas por Carrera (33, 34) | P1, P2 registrar/modificar (selección de Mes; sin descripción) | Unicidad carrera/año/Mes entre cuotas Activo; bloqueo con pagos; precondición "Los Meses deben estar disponibles" |
| E11 | Acta de Examen (21, 22) | P1 Gestión de Actas, P2 registrar/modificar con tribunal | Nro de acta generado; no modificable con inscriptos |
| E12 | CU1 Preinscripción (incluye UI de CU 9) | Asistente de 5 pasos | Alumno `Preinscripto`; documentos opcionales |
| E13 | CU7 Pago de Cuotas + Caja (35, 36) | P1 Gestión de Cajas, apertura, cierre; P2 pago; comprobante PDF con QR | Antes de CU2 para que haya pagos reales (§7 paso 9) |
| E14 | CU2 Inscripción | P1 (Gestión de Alumnos, pestaña Preinscriptos), P2, P3; correo; link de contraseña | Legajo generado; Cuota de Inscripción pagada (`DeudaCuotasService::inscripcionPagada`) |
| E15 | CU3 Inscripción en Comisiones + Cambio (18, 19) | P2 historial, P3 inscripción, P3 cambio, P4 | Correlatividades, capacidad, Caducado/Activo, comisión actual |
| E16 | CU4 Regularidad | P1–P5 | EstadoCurricular siempre creado; "En Curso" solo visual |
| E17 | CU5 Inscripción a Exámenes | P2, P3, comprobante PDF con QR | Reglas Regular/Libre; Policy del Alumno |
| E18 | CU6 Asentamiento | P2, P3, P4 | Aprobado ≥ 4; plan completo → TituloTramite "Sin Gestión" |
| E19 | Auditoría y README (§7 paso 10) | Módulo Auditoría (activity_log), README (composer install, migrate --seed, queue:work, storage:link) | Revisión final, zip del proyecto |

---

## 7. Problemas y decisiones pendientes para Nicolás

1. **Alcance de CU7.** El Anexo D (p. 261) lista 6 CU y no incluye "Registrar Pago de Cuotas"; la skill lo incluye. *Recomendación:* implementarlo (CU2, CU3 y CU5 validan cuotas pagas) y agregarlo al Anexo D.
2. **QR.** `simplesoftwareio/simple-qrcode` 4.2.0 no se puede instalar junto con Fortify 1.41.0 (bacon ^2.0 contra ^3.0; probado con `--dry-run`). *Recomendación:* usar `bacon/bacon-qr-code` 3.1.1 directo con un `QrService` (SVG embebido en el PDF). Alternativa: `endroid/qr-code`.
3. **Versiones por PHP 8.2.4.** Las últimas de spatie/permission (8.x), activitylog (5.x), excel (4.x) y yajra (13.x) piden PHP 8.3/8.4 o Laravel 13. *Recomendación:* aceptar las de §5.1 (6.25.0, 4.12.3, 3.1.70, 12.7.2). Alternativa: actualizar XAMPP a PHP 8.3+.
4. **Contraseña del Personal.** El wireframe P2 de Personal y la especificación (p. 143) piden que el DG cargue la contraseña ("Mínimo 8 caracteres, una letra mayúscula y un número") y hablan de cuenta "DNI y Contraseña". La §1 pide alta sin contraseña con link firmado. *Recomendación:* respetar el wireframe (campo y mensaje de validación), entrar con correo (anotado) y usar el link firmado de 48 h solo para usuarios alumno y para altas del SUPERADMIN.
5. **Medios de pago en CU7. RESUELTO (Nicolás, 2026-10-25): un solo medio de pago por Pago**, como dicen la especificación (paso 15) y los diagramas (Pago 1..1 MedioPago). No se crea `pago_medios`; `pagos.medio_pago_id` NOT NULL. En el wireframe P2 se conservan todos los elementos: "Agregar" fija el único medio (si se agrega otro, reemplaza al anterior), "Importe en <medio>" muestra el importe, "Saldo Pendiente" = Total a Pagar − importe, y "Registrar Pago" se habilita solo con saldo 0 (sin pagos parciales). El cierre de caja agrupa los pagos por `pagos.medio_pago_id`.
6. **Comprobante de pago "Factura C" con CAE** (p. 104): no hay atributos ni integración con AFIP. *Recomendación:* comprobante interno con número `0001-XXXXXXXX` (`LAS_PUNTO_VENTA` + `numero_comprobante`) y QR; CAE fuera de alcance (mostrar "—"), con aclaración en la tesis.
7. **Cuotas: clase Mes. RESUELTO (Nicolás, 2026-10-25; reemplaza la decisión anterior de usar `descripcion`).** Nueva clase Mes (Hoja 4) con valores precargados 1 Inscripción, 2 Marzo … 11 Diciembre (sin Enero, Febrero ni Seguro); Cuota 0..* "corresponde a" 1..1 Mes; Cuota sin `mes` ni `descripcion`. La cuota de Inscripción se identifica por el Mes "Inscripción" (`Mes::INSCRIPCION`), no vence y debe estar pagada para CU2 (alternativo 19: "El alumno no registra el pago de la Cuota de Inscripción"); las de Marzo a Diciembre vencen el día 10 (calculado). Vencimiento y deuda se calculan solo en `App\Services\Finanzas\DeudaCuotasService` (CU2, CU3, CU5, CU6, Pago de Cuotas, Notificar Deuda, Morosidad). Se revirtió el campo "Descripcion" agregado al wireframe de C33/C34: el formulario ya tenía el combo "Mes". En CU7 P2: columna **Concepto** = descripción del Mes en mayúsculas; **Fecha** = fecha del pago en "Cuotas Pagadas" y vencimiento en "Cuotas a pagar" (vacío para Inscripción); un preinscripto (sin legajo) puede pagar su cuota de Inscripción. P1 de Gestión de Cuotas: la columna MES muestra la descripción del Mes.
8. **Becas.** CU7 necesita "Beca activa" y DetallePagoCuota.beca_id, pero Becas está fuera de alcance. *Recomendación:* crear las 4 tablas de becas sin pantallas y cargar datos por seeder; interpretar `valor_beca` como **porcentaje** (el wireframe muestra 10 % de 134.000 = 13.400). Confirmar semántica de `tipoDescuento`/`valorDescuento`.
9. **Qué es "cuota pendiente". RESUELTO por la decisión §7-7.** Pendiente = cuota `Activo` de la carrera no cancelada por un Pago `Activo` del alumno; vencida = mensual (Marzo–Diciembre) pendiente con la fecha actual posterior al día 10 de su mes. La Inscripción nunca cuenta como vencida. El alumno debe las mensuales desde el año lectivo de la Inscripción que pagó. Implementado en `DeudaCuotasService` (`cuotasPendientes`, `cuotasVencidas`, `tieneCuotasVencidas`, `inscripcionPagada`).
10. **Elementos fuera de alcance en wireframes rígidos:** P1 Gestión de Alumnos (íconos Beca, Reporte, ⋮, pestaña Eliminados, ícono "ver"), CU5 P2 ("Dar de Baja"), P1 Personal pestaña Eliminados. *Recomendación:* mostrarlos (el wireframe manda) deshabilitados con tooltip "Fuera del alcance del prototipo"; "Compr." de CU5 sí funciona; Eliminados de Personal = cuentas `Inactivo` (CU 27); Eliminados de Alumnos = legajos `Cerrado` (vacía).
11. **Textos de estado en femenino** en wireframes ("Activa" en Comisiones; "cuenta 'Activa'" en Personal) y colaboraciones ("Activa", "Inactiva", "Abierto"). *Recomendación:* guardar y mostrar `Activo`/`Inactivo` (§3) y `Abierta`/`Cerrada`; anotar en diferencias y corregir los wireframes. Si preferís que el badge diga "Activa", se puede usar solo como etiqueta visual.
12. **Valores de catálogos y selects que la tesis no define:** Modalidad (el wireframe de Acta muestra "Regular" en Modalidad y "Final" en Tipo de Acta, mientras CU5 muestra "Presencial" y "Regular/Libre"), Duración, Llamado, Motivo de baja, TipoGestionTitulo, estado de DocumentoPresentado, sexo, estado civil, niveles de estudio de padres, idiomas. *Recomendación:* los valores de §5.7 (Modalidad = Presencial/Semipresencial/A distancia; TipoActa = Regular/Libre); confirmar o pasar la lista.
13. **Materias anuales y Cuatrimestre obligatorio** (DetallePlanEstudio 1..* — 1..1 Cuatrimestre). *Recomendación:* agregar "Anual" al catálogo Cuatrimestre para mantener NOT NULL (CU4 P2 agrupa por "1er Cuatrimestre, 2do Cuatrimestre y Anual").
14. **CU4 con actor Docente:** no existe relación Docente–Comisión/Materia. *Recomendación:* el Docente ve todas las comisiones (sin filtro) y se anota. Alternativa: agregar `docente_id` a `det_comisiones` (cambio de diagrama).
15. **Acta de Examen: "Docentes asignados a la Materia"** (pp. 138, 140) sin relación en el modelo. *Recomendación:* listar todos los Docentes con cuenta `Activo` en el buscador.
16. **Provincia/Localidad en CU1 P2** son cajas de texto en el wireframe; el modelo exige FK a Localidad. *Recomendación:* mismos labels y posición, pero como select dependiente con búsqueda (Select2).
17. **Alumno.presentaTitulo y tituloSecundario** (diseño de CU2) no están en las 4 hojas. *Recomendación:* no agregarlos; se derivan del DocumentoPresentado "Certificado de Título".
18. **Acceso del Alumno** (CU5, login §1). El alumno se crea en CU1 sin contraseña y solo puede entrar con legajo `Activo`. *Recomendación:* al confirmar CU2 se le envía el link firmado de 48 h para definir la contraseña; en CU5 ve la misma P1 Gestión de Alumnos filtrada por Policy a sus propias filas.
19. **Validaciones sin regla en la tesis:** formato de resolución ministerial (CU 3/4), año del plan (CU 5), carga horaria (CU 6), formato de notas de parciales (CU4: ¿1–10 o 0–10?, ¿decimales?), número de folio (CU6), DNI. *Recomendación:* resolución `^\d{1,5}/\d{4}$`; año del plan entre 1990 y el año actual + 1; carga horaria entera 1–999; notas 1–10 con hasta 2 decimales (igual que CU6); folio entero positivo no usado por otra acta asentada; DNI 7–8 dígitos.
20. **Correlativa "en estado Activo"** (p. 121) sin atributo. *Recomendación:* no agregar estado.
21. **CU 9 Registrar Usuarios Alumno:** su especificación tiene el título equivocado ("CU: Registrar Inscripción del Alumno", p. 122), el actor es "Preceptor / Coordinador" (la lista dice PR) y no tiene colaboración, estados ni wireframes propios. *Recomendación:* implementarlo como servicio que usan P2–P3 de CU1, con permiso para PR y AA; corregir el título en la tesis.
22. **Estados de Carrera:** la especificación usa "Pendiente" y "Activo"; modificar permite cambiar el estado pero no lista los valores. *Recomendación:* `Pendiente`, `Activo`, `Inactivo`.
23. **TituloTramite.tipoGestionTitulo** 1..1, pero CU6 no lo informa. *Recomendación:* nullable.
24. **Caja.montoFinal** no lo carga la colaboración de cierre. *Recomendación:* = suma de los montos contados.
25. **Generación de números:** legajo, nro de acta y nro de comprobante sin formato definido. *Recomendación:* enteros correlativos globales con bloqueo (`LAS_LEGAJO_INICIAL` configurable).
26. **Cambio de Comisión "antes del registro de evaluaciones"** (precondición p. 136) sin mensaje de error. *Recomendación:* bloquear si alguna inscripción activa ya tiene notas o condición final, con el texto que definas (no existe en la tesis).
27. **Detalles de la tesis a corregir:** índice de figuras desfasado; el texto de p. 87 dice "seis hojas" y son cuatro; Fig. 66 se llama "P4 Resumen" pero es P5; la colaboración de CU5 solo dibuja al Preceptor.
28. **Plan de estudio activo único por carrera.** *Recomendación:* sí, validado en el Service.
29. **Documentación faltante ya conocida:** no existe `docs/arquitectura.md` (se usa la §1 de la skill); `soporte/usuario_alumno` solo tiene el análisis "solo atributos"; no hay archivos locales de análisis de C33 ni de C35, pero **sí están en el PDF** (Fig. 115 p. 158 y Fig. 117 p. 161).
30. **CU5 y Comisión sí tienen estados y wireframes en el PDF** (CU5: pp. 97, 100, 101; Comisión: p. 232). Los extraje como imágenes en `C:\Users\Nicolas\AppData\Local\Temp\claude\c--xampp-htdocs-Tesis-Tesis\0b4f34c4-40df-40f3-9649-c2b3e4a5ee29\scratchpad\img\` (`p100_Image1275.png` estados CU5, `p101_Image1277.png` P2, `p101_Image1279.png` comprobante, `p232_Image1853.png` estados Comisión, `p232_Image1854.png` P1, `p232_Image1856.png` P2, `p158_*`, `p161_*`). *Recomendación:* copiarlas a `docs/tesis/CU5_inscripcion_examen/wireframes/` y `docs/tesis/soporte/comision/wireframes/` (o exportar los PNG originales).
31. **Orden CU7 antes de CU2.** *Recomendación:* sí (E13 antes de E14), para que CU2, CU3 y CU5 validen contra pagos reales.
32. **Base de datos de tests.** *Recomendación:* MariaDB `sistema_las_test` (mismo motor que desarrollo) en lugar de SQLite en memoria.
33. **Larastan.** *Recomendación:* sí, 3.13.2 en nivel 5, en dev.
