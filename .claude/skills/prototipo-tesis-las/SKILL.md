---
name: "prototipo-tesis-las"
description: "Planificar, desarrollar y controlar el prototipo Laravel/MySQL de la tesis LAS (7 CU del Anexo D y sus CU de soporte) con agentes planificador, desarrollador y revisor; los wireframes se respetan siempre."
---

# Prototipo Tesis LAS — planificación, desarrollo y control

Usar cuando Nicolás pida planificar, programar, revisar o controlar el prototipo (Laravel + MySQL) de la tesis, o pregunte si el código respeta la tesis.

Casos de uso principales del prototipo (Anexo D):
- CU1 Preinscripción
- CU2 Inscripción
- CU3 Inscripción en Comisiones (y Cambio de Comisión)
- CU4 Regularidad Académica por Materia
- CU5 Inscripción a Exámenes
- CU6 Asentamiento de Acta
- CU7 Registrar Pago de Cuotas (con Apertura y Cierre de Caja)

Fuera de alcance: Gestionar Trámite de Título (CU6 crea el trámite en "Sin Gestión"; no hay pantalla para gestionarlo). Becas, equivalencias, reportes y cierre de legajo tampoco, salvo que Nicolás lo pida.

El sistema tiene TRES capas:
- **Base común** (no está en los wireframes, es de todo sistema): login, recuperación de contraseña, menú lateral, encabezado, usuarios y roles, auditoría (§1).
- **CU de soporte**: los CU de la tesis que cargan los datos que los CU principales necesitan como precondición (§6).
- **CU principales**: los 7 del Anexo D (§2 a §5).

El trabajo lo hacen agentes con roles separados (§8): Planificador → Desarrollador → Revisor, con un Coordinador (la sesión principal) que los lanza, une resultados y habla con Nicolás.

## 0. Fuentes de verdad y qué se puede cambiar
**Rígido (no se cambia nunca):**
- **Wireframes y diagrama de estados del CU (Anexo 3)**: cada pantalla, sus campos, filtros, columnas, botones, textos, orden y la navegación entre pantallas se implementan tal cual. No se agregan, quitan, renombran ni mueven elementos, ni se suman pantallas o pasos, aunque parezca mejor UX. Si un wireframe es imposible de implementar o contradice la especificación, se detiene y se consulta a Nicolás.
- **Comportamiento de la especificación (Anexo 1)**: lo que el sistema hace en cada paso, las validaciones de los escenarios alternativos y sus mensajes exactos, y los valores/estados de §3.

**Flexible (puede cambiar con criterio y buenas prácticas):**
- Todo lo demás: estructura del código, nombres internos, clases auxiliares, orden interno de operaciones, índices, tablas auxiliares, componentes, estilos visuales, colores, íconos de AdminLTE, comportamiento responsive, feedback (spinners, confirmaciones, toasts, estados vacíos), y la forma de implementar lo que piden los diagramas de clases y de colaboración.
- Los diagramas de análisis, diseño y colaboración guían el modelo de datos y la lógica, pero si una buena práctica pide otra cosa (una tabla intermedia, un campo técnico, otro reparto de métodos), se puede cambiar. Toda diferencia con un diagrama se anota en docs/diferencias-tesis.md (qué dice el diagrama, qué se hizo y por qué) para que Nicolás decida si ajusta la tesis. No bloquea el trabajo.

Fuentes, en orden: especificación, wireframes y estados, diagrama de clases de análisis, diseño del CU y el de 4 hojas (última versión corregida), colaboración, arquitectura de referencia (§1) y buenas prácticas (§1b). Si falta alguna, pedirla.

## 1. Arquitectura de referencia (igual al sistema que se migra)
**Stack**
- PHP 8.2+, Laravel 12 con estructura nueva (middleware, alias y rutas en bootstrap/app.php; sin Kernel.php ni RouteServiceProvider).
- MySQL/MariaDB (XAMPP en desarrollo).
- .env: SESSION_DRIVER=database, CACHE_STORE=file, QUEUE_CONNECTION=database, APP_LOCALE=es, APP_FALLBACK_LOCALE=es, APP_TIMEZONE=America/Argentina/Buenos_Aires.
- AdminLTE 3 (jeroennoten/laravel-adminlte, Bootstrap 4 + jQuery) para todo, incluido el login. Nada de Tailwind ni Livewire. Vite solo para CSS/JS propio.

**Paquetes** (versiones fijas, nunca "*" ni dev-master)
- Obligatorios: laravel/fortify, spatie/laravel-permission, jeroennoten/laravel-adminlte, yajra/laravel-datatables-oracle, barryvdh/laravel-dompdf, simplesoftwareio/simple-qrcode, maatwebsite/excel, spatie/laravel-activitylog, guzzlehttp/guzzle.
- Dev: laravel/pint, phpunit, spatie/laravel-ignition; recomendado larastan si Nicolás lo acepta.
- NO: Jetstream, Livewire, Sanctum, paquetes SOAP de terceros, encriptación externa, predis.

**Autenticación (solo Fortify)**
- Login con CORREO ELECTRÓNICO y contraseña (no con DNI). En config/fortify.php 'username' y 'email' apuntan a la columna del correo del usuario, única y obligatoria.
- Features: resetPasswords y emailVerification; sin registro público. Vistas Blade con estilo AdminLTE.
- authenticateUsing: buscar usuario por correo con cuenta activa → si tiene rol Alumno, verificar que tenga un Alumno con legajo activo (si no: "Este usuario no está activo") → Hash::check.
- Rate limit: 5 intentos por minuto por correo+IP.
- Notificaciones de reset y verificación propias, en español y encoladas.
- Alta de usuario por administrador: sin contraseña + link firmado de 48 h al correo (temporarySignedRoute, middleware 'signed', de un solo uso).
- Logout por POST con CSRF.

**Autorización (obligatoria)**
- Toda ruta de gestión con login Y permiso en la ruta o en el controller. Ocultar en el menú NO alcanza.
- Policies para reglas por registro (el alumno solo ve lo suyo).
- Permisos {accion}_{entidad}: listar_x, ver_x, crear_x, editar_x, eliminar_x, exportar_x.
- SUPERADMIN con Gate::before. Seeder idempotente de roles/permisos + admin inicial desde .env.
- Menú (config/adminlte.php) con 'can' usando los mismos permisos.
- Tests: invitado → login; sin permiso → 403; con permiso → 200.

**Rutas**
- routes/web.php: solo lo público ("/" → login, verificación de email, definir contraseña, validación de constancias/comprobantes por QR con UUID).
- routes/panel.php con ['web','auth','verified'], prefijo 'panel', nombre 'panel.'; agrupado por módulo con su permiso. Lo que modifica datos va por POST/PUT/DELETE. DataTables: POST panel/{modulo}/datatable.

**Código**
- Controllers delgados; FormRequest por acción con mensajes en español; Models con $table, $primaryKey y $fillable explícitos; Services con la lógica del CU; Policies; Mail; Exports; componentes Blade.
- SQL crudo solo con bindings.
- SoftDeletes para bajas de entidades principales.
- Auditoría con spatie/laravel-activitylog (LogsActivity, logOnlyDirty, causer) y módulo "Auditoría" en el panel.

**Vistas**
- @extends('adminlte::page'); mensajes flash con un único <x-alert />; PDF con fecha en español y QR a la ruta pública de validación.
- El patrón index del sistema de referencia (pestañas Activos/Eliminados + DataTable + modales) se usa SOLO en pantallas sin wireframe; si hay wireframe, manda el wireframe.

## 1b. Buenas prácticas obligatorias (todo agente que programe)
**Código limpio y sin repetir (DRY)**
- Antes de escribir algo, buscar si ya existe en el proyecto (componente, trait, service, scope, regla) y reutilizarlo. Si un bloque se repite 2 veces, extraerlo.
- Reutilizables mínimos: componentes Blade (<x-alert>, <x-header-with-title>, <x-form.input>, <x-form.select>, <x-form.date>, <x-btn-accion>, <x-modal-confirmar>, <x-datatable>, <x-badge-estado>); parcial de formulario compartido entre crear y editar; JS común para DataTables (español, responsive) y confirmaciones.
- En PHP: traits, scopes de Eloquent, Enums PHP para cada estado/valor de §3, Rules propias, Services/DataTables base cuando varios módulos comparten lógica.
- SOLID: controller → FormRequest → Service → Model; inyección de dependencias; nada de lógica de negocio en vistas, rutas ni controllers.
- Tipado, nombres claros en español para el dominio, sin código muerto ni comentarios obvios.
- Rendimiento: eager loading (sin N+1), índices en FK y búsquedas, DataTables server-side, transacciones cortas, colas para correos y PDF.
- Configuración en config/.env, nunca valores mágicos.
- Pint antes de entregar; tests verdes; migraciones reversibles.

**Diseño responsive (sin cambiar el wireframe)**
- Mobile-first con la grilla de Bootstrap 4; probar en 375 px, 768 px y 1280 px. En celular los elementos del wireframe pueden apilarse, pero son los mismos y en el mismo orden.
- Tablas con DataTables Responsive: sin scroll horizontal de la página.
- Menú lateral colapsable; PDF y correos legibles en celular.

**UX/UI (solo lo que no altera el wireframe)**
- Consistencia: mismos estilos de títulos, botones y colores de estado (badge por estado) en todo el sistema.
- Formularios: labels visibles, obligatorios marcados (*), placeholders, Select2 en listas largas, datepicker en español, máscaras para DNI/montos, errores debajo de cada campo con el texto de la especificación, conservar lo cargado (old()).
- Feedback: confirmación (SweetAlert2) antes de acciones destructivas o irreversibles (baja, caducar, asentar, cerrar caja), botón deshabilitado + spinner al enviar, mensaje de éxito, estados vacíos y de carga.
- Íconos con texto o tooltip; accesibilidad (contraste, foco visible, teclado, labels asociados, aria-label).
- Textos en español, formatos argentinos (dd/mm/aaaa, $ 1.234,56).
- Nada de esto puede agregar, quitar o mover campos, botones o pantallas del wireframe.

## 2. Cómo encaja la tesis en esa arquitectura (guía, flexible)
- **Usuario de la tesis = modelo User**. Acceso con correoUsuario (único) y contraseña; estadoCuenta controla el acceso; dniUsuario es dato único pero no se usa para entrar. Si la tesis dice "DNI + contraseña", anotarlo en docs/diferencias-tesis.md.
- **Roles = actores de la tesis**: DirectorGeneral (DG), Coordinador (CC), Preceptor (PR), AuxiliarAdministrativo (AA), Docente, Cajero (CJ), Alumno (AL) + SUPERADMIN técnico. Un rol por usuario (syncRoles). La clase Rol se implementa con la tabla roles de Spatie.
- **Permisos por CU**: los actores de cada CU en la lista de CU de la tesis reciben el permiso (CU5 → PR y AL; CU6 → PR; CU7 y Caja → CJ; Carrera/Materia/Plan → CC). Un ítem de menú por CU.
- **Nombres**: tablas y columnas en snake_case; docs/trazabilidad.md con atributo de la tesis → columna.
- **Estados**: columnas con los valores exactos de §3 (Enums); SoftDeletes solo para bajas.
- **Multiplicidades** como guía de FK (1..1 = NOT NULL; 0..1 = nullable; 1..* se valida en el servicio).
- **Colaboración** como guía del Service: crear/cargar/vincular/actualizar/calcular; todo el CU en DB::transaction.
- **Validaciones** de la especificación implementadas (FormRequest + Service) con el mensaje exacto.
- **Cuota de inscripción**: se identifica por su relación con el Mes "Inscripción" (una sola constante con la descripción), nunca por un número fijo en el código ni por texto libre.

## 3. Valores y estados acordados (Enums, rígidos)
- Estado activo siempre "Activo" (masculino); "Caducado". Excepción: Caja "Abierta"/"Cerrada".
- Alumno.situacionEstudios: Completo / En curso / Incompleto. El legajo es por carrera.
- AlumnoComision: "Activo"/"Caducado"; tipoInscripcion = "Por Comisión".
- Comisión actual: relación Alumno "cursa actualmente en" Comision (0..1); se actualiza en Inscripción en Comisiones y Cambio de Comisión (este caduca los AlumnoComision vigentes y crea nuevos).
- CU4: condicionFinal la elige el actor (Regular/Libre); "En Curso" solo se muestra. Siempre se crea un EstadoCurricular.
- CU5: actor Alumno / Preceptor; actas "Libre" sin condiciones académicas; DetalleActa con estadoDetalleActa = "Activo"; "Dar de Baja" fuera de alcance.
- ActaExamen: se crea "Pendiente" con asentada = 'N'; al asentar → "Finalizado", asentada = 'S' y nroFolio.
- CU6: nota 1 a 10; "Aprobado" si nota ≥ 4, si no "Desaprobado" (lo calcula el sistema). EstadoCurricular con nroActa. Si "Aprobado" y completó el plan → TituloTramite "Sin Gestión" y Alumno "Título en Trámite".
- CU7: Pago en Caja "Abierta" del Cajero; 1..* DetallePagoCuota (cada uno cancela una Cuota), MedioPago, FacturaComprobante (PDF con QR); la Beca puede bonificar. El cierre de caja genera DetalleCierreCaja por medio de pago. Revisar la consistencia de CU7 en la tesis antes de programarlo.
- Cuotas y Mes:
  1. Modelo (Hoja 4 de diseño): clase Mes (idMes, descripcion; buscar(idMes), ubicar()). Cuota 0..* "corresponde a" 1..1 Mes. Cuota NO tiene mes ni descripcion: idCuota, monto, anio, estadoCuota + relación con Carrera y con Mes; Cuota.vincular() recibe el vínculo de Carrera y de Mes.
  2. Valores de Mes (precargados, sin CU propio, en orden; el id es el orden): 1 Inscripción, 2 Marzo, 3 Abril, 4 Mayo, 5 Junio, 6 Julio, 7 Agosto, 8 Septiembre, 9 Octubre, 10 Noviembre, 11 Diciembre. No hay Enero, Febrero ni Seguro.
  3. Reglas (Aclaración "Meses y vencimiento de las Cuotas" en Registrar Cuotas por Carrera): por cada Carrera y año lectivo se registra una cuota por cada Mes; Carrera + año lectivo + Mes es único entre cuotas "Activo". La cuota de Inscripción no tiene vencimiento mensual y debe estar pagada para registrar la Inscripción del Alumno (CU2). Las cuotas de Marzo a Diciembre vencen el día 10 de su mes (se calcula, no se guarda). Una cuota está vencida si la fecha actual es posterior a su vencimiento y no está cancelada por un Pago "Activo". La cuota de Inscripción nunca cuenta como vencida: no suma en nivel de deuda, morosidad ni en los controles de cuotas de CU3, CU5 y CU6. Supuesto: la inscripción se hace antes del inicio del ciclo lectivo; el alumno debe todas las cuotas mensuales del año (no se guarda fecha de inscripción).

## 4. Correo al alumno
- Un Mailable por caso (layout común): CU2 inscripción, CU3 comisión asignada (código y horario), CU4 condición cargada, CU5 inscripción a examen, CU6 resultado; CU7 comprobante solo si la especificación lo indica.
- Destinatario: correo del alumno. Mail::queue() con afterCommit; un fallo de correo no revierte el CU. En desarrollo MAIL_MAILER=log o Mailtrap.

## 5. Pantallas
- Cada pantalla del wireframe (P1..Pn) = una vista Blade dentro del layout AdminLTE con exactamente los mismos campos, filtros, columnas, botones, textos y orden; la navegación sigue el diagrama de estados.
- Errores = mensajes de los escenarios alternativos.
- Login, menú, encabezado, perfil, usuarios/roles y auditoría NO están en los wireframes: son la base común (estilo libre dentro de AdminLTE). Proponer una Aclaración general para el Anexo 3 si no la tiene.
- CU de soporte: si tienen wireframe, se respeta igual; si no, patrón index del sistema de referencia.

## 6. CU de soporte (cargan las precondiciones de los CU principales)
Con pantalla (número = lista de CU de la tesis):
- 25 Registrar datos del Personal (DG) y 26 Modificar (DG). 27 Baja opcional.
- 9 Registrar Datos de Usuarios Alumno (PR).
- 3 Registrar Carrera y 4 Modificar Carrera (CC).
- 1 Registrar Materia y 2 Modificar Materia (CC).
- 5 Registrar Plan de Estudio y 6 Registrar Materia al Plan (CC). 7 Baja de Materia del Plan opcional.
- 8 Registrar Correlatividades de Materia (CC).
- 17 Registrar Comisión (CC).
- 21 Registrar Acta de Examen y 22 Modificar Acta de Examen (CC).
- 33 Registrar Cuotas por Carrera (CC–DG) y 34 Modificar.
- 35 Apertura de Caja y 36 Cierre de Caja (CJ): parte de CU7.
Sin CU propio, solo seeders (y opcionalmente un ABM genérico para el SUPERADMIN): Modalidad, Duración, Régimen, Cuatrimestre, Llamado, TipoActa, RolTribunal, TipoDocumento, Provincia/Localidad, Motivo, MedioPago, TipoGestionTitulo, Mes.

## 7. Orden de trabajo
1. composer.json / package.json con versiones fijas y .env.example.
2. bootstrap/app.php (rutas web y panel, alias de Spatie).
3. Componentes y JS reutilizables de §1b antes de los módulos.
4. Fortify (login por correo, vistas AdminLTE, authenticateUsing, alta con link firmado).
5. Spatie: roles, permisos por CU, Gate::before, módulo Usuarios y Roles.
6. Layout AdminLTE y menú agrupado: Académico, Alumnos, Exámenes, Finanzas, Personal, Administración.
7. Migraciones, Enums y modelos, seeders sin CU y factories.
8. CU de soporte: Personal → Usuarios Alumno → Carrera → Materia → Plan → Materia al Plan → Correlatividades → Comisión → Cuotas por Carrera → Acta de Examen.
9. CU principales CU1 → CU6 y CU7 (CU7 puede ir antes de CU2 si los controles de cuotas necesitan pagos reales).
10. Módulo de Auditoría y README (composer install, migrate --seed, queue:work, storage:link).
No marcar nada terminado con tests fallando ni con Pint sin pasar.

## 8. Agentes
**Coordinador (sesión principal)**
- Mantiene la lista de tareas, lanza los agentes con Agent, espera sus notificaciones (no predice resultados), une resultados, verifica los hallazgos Alta y habla con Nicolás.
- Ciclo por CU: Planificador → aprobación de Nicolás → Desarrollador → Revisor → correcciones → cierre. Si Nicolás dice que no hace falta aprobar cada plan, sigue directo y solo se detiene ante una decisión pendiente o un problema con un wireframe.

**Agente Planificador** (antes de cada CU y una vez al inicio para el plan general). NO escribe código. Recibe especificación, wireframes, diagrama de estados, análisis, diseño del CU, diseño de 4 hojas y colaboración, estas reglas y lo que ya existe en el proyecto. Escribe docs/planes/<CU>.md con:
1. Actor(es) → roles y permisos; precondiciones → datos/CU de soporte que deben existir (si falta algo, bloqueante).
2. Inventario de pantallas del wireframe: por cada P#, cada campo, filtro, columna, botón y texto, y la navegación del diagrama de estados. Este inventario es la lista de control de la UI.
3. Matriz de trazabilidad, una fila por paso del sistema: Paso | Pantalla (P#) y elemento | Mensaje(s) de la colaboración | Clase y operación del diseño | Archivo/método a crear o reutilizar | Test.
4. Escenarios alternativos: condición → validación → mensaje exacto → test.
5. Modelo de datos a crear/usar y Enums de §3; diferencias propuestas respecto de los diagramas (con motivo) para docs/diferencias-tesis.md.
6. Reutilización: qué ya existe y se usa; qué nuevo se crea y por qué.
7. Correo/PDF si corresponde.
8. Lista ordenada de archivos y tests.
9. Problemas en wireframes o especificación (contradicciones, cosas imposibles) y decisiones pendientes para Nicolás.

**Agente Desarrollador**
- Implementa el plan aprobado cumpliendo §1, §1b y §2–§5. Las pantallas se construyen elemento por elemento contra el inventario del wireframe.
- Puede cambiar libremente lo flexible (§0) si mejora el código, anotando en docs/diferencias-tesis.md lo que difiera de un diagrama.
- Si algo del wireframe o de la especificación no se puede cumplir, se detiene y lo informa.
- Corre migraciones en limpio, `php artisan test` y Pint; actualiza docs/trazabilidad.md y marca en el plan cada fila implementada (archivo:línea).

**Agente Revisor** (no escribió el código ni el plan). Devuelve una tabla:
CU | Severidad | Artefacto y ubicación | Código (archivo:línea) | Qué no coincide | Corrección propuesta.
Severidad:
- **Alta**: la pantalla no coincide con el wireframe (falta, sobra, cambia de nombre o de orden un campo, filtro, columna, botón, texto o pantalla; navegación distinta al diagrama de estados); el comportamiento no coincide con la especificación (paso, validación, mensaje, estado de §3); ruta sin auth/permiso.
- **Media**: viola una buena práctica (código duplicado, lógica fuera de lugar, N+1, sin transacción), problema responsive o de UX/accesibilidad, test faltante.
- **Baja / Informativa**: diferencia con un diagrama de clases o de colaboración que no está anotada en docs/diferencias-tesis.md (pedir anotarla, no revertirla).
Chequeos obligatorios:
- Inventario de pantallas del plan ↔ vistas, elemento por elemento, con capturas de Playwright a 375 px y 1280 px puestas al lado del wireframe.
- Cada paso y escenario alternativo de la especificación ↔ código y test, con el mensaje exacto.
- Estados y valores de §3.
- Auth + permiso en todas las rutas; tests 302/403/200.
- Buenas prácticas, responsive y UX de §1b.
El Coordinador verifica los hallazgos Alta contra el material antes de reportarlos y descarta falsos positivos; los válidos vuelven al Desarrollador.

## 9. Entrega
- Por CU, en el chat: el plan (resumen + decisiones pendientes) antes de programar; después, qué quedó hecho, hallazgos del Revisor (con capturas si hay diferencias visuales) y el resumen de docs/diferencias-tesis.md para que Nicolás decida si ajusta la tesis (texto listo para pegar cuando corresponda).
- El código como zip del proyecto. No cambiar la tesis ni los diagramas sin confirmación de Nicolás.