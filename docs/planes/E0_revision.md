# Revisión E0 — Base común

Autor: Agente Revisor (no escribió el código ni el plan). Fecha: 2026-10-25.
Alcance: plan `docs/planes/00_general.md` §2, §3.2, §5 y fila E0 de §6; skill §0, §1, §1b, §2, §8.
E0 no tiene wireframes: login, menú, usuarios y roles son base común (estilo libre dentro de AdminLTE).

## Verificaciones ejecutadas

- `php artisan test`: **48 tests, 411 aserciones, todos en verde**. `vendor/bin/pint --test`: **passed**.
- Pruebas propias, desde el scratchpad y sin tocar el proyecto (`.../scratchpad/revision_e0/RevisionE0Test.php`, se corrieron contra `sistema_las_test`). Las 3 **confirman** los hallazgos 1, 2 y 3:
  1. un usuario dado de baja (`Inactivo`) que ya tiene sesión iniciada sigue entrando al panel con 200;
  2. un Director General al que se le da `crear_usuario` puede crear un usuario SUPERADMIN;
  3. al modificar un usuario Alumno, el formulario obliga a cambiarle el rol.
- Capturas con Playwright 1.49.1 (Chromium) a 375 px y 1280 px (lista al final). En ninguna pantalla hay scroll horizontal: se midió `scrollWidth` contra `innerWidth`. El único error de consola es el 404 esperado de `/panel/no-existe`.
- Lo que está bien:
  - **Versiones exactas.** composer.json y package.json no tienen `^`, `*` ni dev-master, salvo `php ^8.2`, que el plan permite.
  - **Paquetes prohibidos.** No están Jetstream, Livewire, Sanctum, predis, simple-qrcode, Sail ni Tailwind (tampoco en `package-lock.json`). `laravel/passkeys` entra como dependencia transitiva de Fortify y la feature está desactivada.
  - **Estructura Laravel 12.** Middleware y rutas en `bootstrap/app.php`; no existen Kernel ni RouteServiceProvider.
  - **Configuración.** `.env.example` coincide con §5.2 y `ADMIN_PASSWORD` está vacío. `.env` está en `.gitignore`. Fortify y AdminLTE coinciden con §5.4 y §5.5.
  - **Permisos y menú.** `config/permisos.php` tiene los 52 permisos y la matriz de los 8 roles exactamente como §2.2 (verificado fila por fila). El menú coincide con §2.3, con `can` en cada ítem.
  - **Rutas.** Todas las rutas de `routes/panel.php` tienen `auth` + `verified` + `can:`, salvo `panel.inicio`, que solo exige login. Las mutaciones van por POST/PUT/PATCH/DELETE y el DataTable por POST.
  - **Autenticación.** `Gate::before` para SUPERADMIN. Logout solo por POST con CSRF: el menú de usuario de AdminLTE y la vista de verificación usan un form. Link de alta firmado de 48 h y de un solo uso. Login por correo; el DNI no sirve para entrar. Sin registro público.
  - **Otros.** No hay SQL crudo. `$fillable` explícitos. Notificaciones propias encoladas con `afterCommit`.

## Hallazgos

| CU | Severidad | Artefacto y ubicación | Código (archivo:línea) | Qué no coincide | Corrección propuesta |
|---|---|---|---|---|---|
| Base (login/usuarios) | **Alta** | Acceso con cuenta `Inactivo` | `app/Services/Usuarios/UsuarioService.php:59-66`; `bootstrap/app.php:26-35` (no hay middleware de estado); `resources/views/auth/login.blade.php:14` ("Recordarme") | Skill §2: "estadoCuenta controla el acceso". `estado_cuenta` solo se mira al iniciar sesión (`AutenticacionService.php:26-29`). Un usuario dado de baja conserva su sesión y, con "Recordarme", la cookie de recuerdo lo vuelve a autenticar sin pasar por `authenticateUsing`. Verificado: un Cajero `Inactivo` con sesión iniciada recibe 200 en `panel.cajas.index`. | Agregar un middleware `cuenta.activa` al grupo del panel (`bootstrap/app.php`) que, si `! $request->user()->estaActivo()`, haga `Auth::logout()`, invalide la sesión y redirija a login con "Este usuario no está activo". Opcional: en `darDeBaja`, regenerar `remember_token` y borrar las filas de `sessions` del usuario. Agregar el test "usuario dado de baja con sesión abierta → redirige a login". |
| Base (usuarios) | Media | Asignación del rol SUPERADMIN desde Usuarios | `app/Enums/RolSistema.php:44-50`; `app/Http/Requests/Administracion/GuardarUsuarioRequest.php:39-43`; `app/Http/Controllers/Panel/Administracion/UsuarioController.php:88-97` | Los permisos 39–46 se pueden dar a cualquier rol desde la pantalla Roles (plan §2.2, nota). Un usuario que no es SUPERADMIN y tiene `crear_usuario`/`editar_usuario` puede asignar SUPERADMIN, y con eso escala a acceso total por `Gate::before`. Verificado: un DG con `crear_usuario` creó un SUPERADMIN. | Ofrecer y aceptar `SUPERADMIN` solo si el actor es SUPERADMIN: filtrarlo en `rolesAsignables()` y agregar `Rule::notIn(['SUPERADMIN'])` condicional en el FormRequest. Agregar un test. |
| Base (usuarios) | Media | Modificar un usuario con rol Alumno | `resources/views/panel/administracion/usuarios/_acciones.blade.php:2`; `GuardarUsuarioRequest.php:41`; `app/DataTables/UsuariosDataTable.php:17-20` | El listado muestra los usuarios Alumno con el botón Modificar, pero el select no ofrece "Alumno" y el FormRequest lo rechaza: para guardar hay que cambiarle el rol. Verificado: PUT con `rol=Alumno` → error en `rol`. Desde E12 esto romperá a los alumnos (pierden el rol). | Excluir los usuarios Alumno del listado de Usuarios (se gestionan desde CU1/CU 9), o mostrarlos sin Modificar ni cambio de rol (permitir mantener el rol actual si ya es Alumno). |
| Base (tests) | Media | `RutasPanelTest` (§5.8: "dataset de todas las rutas `panel.*`") | `tests/Feature/Autorizacion/RutasPanelTest.php:24-26`, `:68-81` | El 302/403/200 solo se prueba en rutas GET **sin parámetros**. Quedan sin prueba de comportamiento `panel.usuarios.edit`, `panel.roles.edit`, los dos `datatable` (salvo usuarios con Preceptor) y todas las mutaciones (store/update/destroy/reactivar/reenviar-link). Para estas el test solo comprueba que exista un middleware `can:`, no que devuelvan 302 (invitado) / 403 (sin permiso). | Armar un dataset de todas las rutas `panel.*` que resuelva los parámetros con factories (usuario y rol) y que pruebe con el verbo real: invitado → 302 a login, sin permiso → 403, con permiso → 200/302. |
| Base (UX/accesibilidad) | Media | Errores de validación de los campos | `resources/views/components/form/input.blade.php:17` (igual en `select`, `date`, `textarea`, `file`); `components/form/grupo.blade.php:12` | El `<span id="{id}-error">` no está asociado al campo: `aria-describedby` solo apunta a la ayuda y no existe en select/date/textarea/file. El lector de pantalla no lee el error al enfocar el campo (§1b "labels asociados, aria"). | Armar `aria-describedby` con `{id}-ayuda` y/o `{id}-error` según existan, en todos los componentes `x-form.*` (una sola vez en un helper o en `grupo`). |
| Base (UX) | Media | Formulario de Roles: grupos y nombres de permisos | `app/Http/Controllers/Panel/Administracion/RolController.php:76-83`; `resources/views/panel/administracion/roles/_form.blade.php:32-34`; captura `roles_editar_1280.png` | Las leyendas se generan desde el nombre técnico y salen sin tildes: "Preinscripcion", "Inscripcion comision", "Comision", "Catalogo", "Auditoria", "Inscripcion examen". Cada casilla muestra el identificador `listar_materia`, etc. Esto incumple §1b: textos en español y consistentes. | Agregar en `config/permisos.php` (o en un Enum) etiquetas legibles por entidad y por permiso ("Comisión", "Listar materias"…) y mostrar el identificador técnico solo como tooltip. |
| Base (UX) | Media | Conservar lo cargado (`old()`) en checkboxes | `resources/views/components/form/checkbox.blade.php:7` | Si se destildan **todos** los permisos y la validación falla (p. ej. nombre duplicado), `old('permisos')` es null y el formulario vuelve a marcar los permisos guardados, perdiendo lo que cargó el usuario (§1b "conservar lo cargado"). | Usar `session()->hasOldInput()` para decidir si aplicar `old()`: con old input y sin la clave → no marcado. |
| Base (tests) | Media | `RolesPermisosSeederTest` | `tests/Feature/Seeders/RolesPermisosSeederTest.php:30-40` | `test_cada_rol_tiene_exactamente_los_permisos_del_plan` compara la BD contra `config/permisos.php`, que es la misma fuente que usa el seeder: es tautológico. La matriz del plan solo se controla por cantidades (`:42-59`). Hoy la config coincide con §2.2 (verificado a mano), pero un cambio en la config no rompería el test. | Escribir en el test la matriz de §2.2 literal (rol → lista de permisos) y compararla con la BD. |
| Base (tests) | Media | `LoginTest` rate limit por correo+IP | `tests/Feature/Auth/LoginTest.php:104-117` | Solo prueba que el 6.º intento con el mismo correo se bloquea. No prueba que la clave sea correo+IP, es decir, que otro correo desde la misma IP no quede bloqueado. Es lo que justifica el desvío "limitador propio de Fortify". | Agregar una aserción: tras 5 fallos de `a@…`, el login de `b@…` funciona. |
| Base (UX) | Baja / Informativa | Confirmación de bajas | `resources/views/panel/administracion/usuarios/index.blade.php:26-29`, `roles/index.blade.php:26-29`; `usuarios/_acciones.blade.php:4-5` | Hay confirmación antes de la baja (modal Bootstrap `<x-modal-confirmar>`), pero §1b nombra SweetAlert2 y "Reenviar enlace" sí usa SweetAlert. Son dos estilos de confirmación en la misma pantalla. No es un error funcional. | Unificar: o todas las acciones destructivas con SweetAlert (`confirmar=`), o todas con `<x-modal-confirmar>`. |
| Base (usuarios) | Baja / Informativa | Cambio del propio rol | `app/Services/Usuarios/UsuarioService.php:43-57` | Se impide darse de baja a uno mismo, pero no cambiarse el rol. Un SUPERADMIN puede quitarse SUPERADMIN y quedar sin acceso a Administración (o dejar el sistema sin SUPERADMIN). | Rechazar el cambio de rol del propio usuario, o al menos impedir quitar el último SUPERADMIN. |
| Base (seguridad) | Baja / Informativa | `$fillable` de `User` | `app/Models/User.php:32-49` | Incluye `password`, `estado_cuenta` y `fecha_alta`. Hoy no se puede explotar: los FormRequest pasan `safe()->except/only` con campos validados. Pero cualquier `fill($request->all())` futuro permitiría fijar contraseña o estado. | Sacar `password`, `estado_cuenta` y `fecha_alta` de `$fillable` y asignarlos con `forceFill` en `UsuarioService`/`AdminSeeder` (como ya hace `definirContrasenia`). |
| Base (seguridad) | Baja / Informativa | Disco `local` con `serve => true` | `config/filesystems.php:36` | Registra `GET/PUT storage/{path}` (`storage.local`, `storage.local.upload`). Están protegidas con URL firmada, pero E0 no las usa y no forman parte de §1. | Poner `'serve' => false` mientras no se usen URLs temporales del disco local. |
| Base (AdminLTE) | Baja / Informativa | Logo | `config/adminlte.php:24-25`; captura `login_375.png` | Plan §5.5: "logo con nombre del instituto". Se usa la imagen por defecto `AdminLTELogo.png` (una "A") con el texto "Sistema LAS". | Cambiar `logo_img` por un logo propio (o quitarlo) y usar `logo_img_alt`/texto con `LAS_INSTITUTO_NOMBRE`. |
| Base | Baja / Informativa | composer.json | `composer.json:72` (`post-create-project-cmd`), `:44-51` (`setup`) | Quedó el script del esqueleto que crea `database/database.sqlite` (el proyecto usa MariaDB), y `setup` no corre `db:seed`. | Quitar la línea de SQLite y agregar `@php artisan db:seed --force` a `setup` (o documentarlo en el README de E19). |
| Base (desvíos) | Baja / Informativa | Desvíos declarados no anotados | `config/fortify.php` (`'limiters' => ['login' => null]`); `components/form/date.blade.php:3`; `phpunit.xml:21` | El limitador propio de Fortify (en lugar de `RateLimiter::for` del plan §5.4), que `<x-form.date>` envíe dd/mm/aaaa y `APP_URL=http://localhost` en tests no figuran en `docs/diferencias-tesis.md` ni en el plan. No son diferencias con un diagrama, pero sí con el plan. | Anotarlos en el plan (§5.4/§5.6) o en una sección "Desvíos del plan" de `docs/diferencias-tesis.md`. |

## Evaluación de los desvíos declarados por el Desarrollador

| Desvío | Evaluación |
|---|---|
| Limitador propio de Fortify (`LoginRateLimiter`) en vez de `RateLimiter::for` | **Aceptable.** Fortify usa la clave `lower(email)|ip` con 5 intentos por minuto: cumple la §1 y devuelve el error en el formulario en lugar de un 429. Falta el test de la clave correo+IP y anotarlo (ver tabla). |
| Comprobar la contraseña antes que el legajo | **Aceptable** y mejor en seguridad: no revela qué cuentas existen. Está anotado en `docs/diferencias-tesis.md`. |
| Rutas provisorias con `ModuloPendienteController` y su permiso definitivo | **Aceptable.** Mantienen nombre y `can:` definitivos, el menú §2.3 funciona y `RutasPanelTest` las cubre. Cada etapa debe reemplazar su línea en `routes/panel.php:66-83`. |
| No asignar rol Alumno desde Usuarios; roles de la tesis no renombrables ni dados de baja | **Aceptable** y anotado. Pero falta coherencia en el listado: hallazgo Media "Modificar un usuario con rol Alumno". |
| `<x-form.date>` envía dd/mm/aaaa | **Aceptable** (formato argentino §1b) siempre que cada FormRequest use `date_format:d/m/Y` y convierta a `Y-m-d` en el Service. Conviene un helper o regla común para no repetir la conversión. Anotar. |
| `APP_URL=http://localhost` en `phpunit.xml` | **Aceptable**: evita que las URL firmadas dependan de la carpeta de XAMPP. Anotar. |
| `VerificadorLegajoAlumno` que devuelve false en E0 | **Aceptable**: E0 no tiene tabla `alumnos` y el contrato permite cambiarlo. **Riesgo:** si E1/E12 no cambian el binding (`AppServiceProvider.php:17-19`), ningún alumno podrá entrar. Agregar ese cambio al criterio de cierre de E1/E14, con un test sin mock. |

## Resumen

- **Alta: 1.** Una cuenta `Inactivo` con sesión iniciada o con "Recordarme" sigue entrando al panel.
- **Media: 8.** Escalada a SUPERADMIN desde Usuarios; edición de usuarios Alumno; tests 302/403/200 incompletos (rutas con parámetros y mutaciones); `aria-describedby` de errores; textos sin tilde y nombres técnicos en Roles; `old()` en checkboxes; test del seeder tautológico; test del rate limit por correo+IP.
- **Baja / Informativa: 7.**
- Responsive: login, panel, menú, Usuarios (Activos/Eliminados/Crear con errores) y Roles (índice/crear/editar) se ven bien a 375 y 1280 px. No hay scroll horizontal, el menú es colapsable, el foco es visible (contorno ámbar) y la DataTable es Responsive (columnas plegadas con "+" a 375 px).

### Capturas generadas
Carpeta: `C:\Users\Nicolas\AppData\Local\Temp\claude\c--xampp-htdocs-Tesis-Tesis\0b4f34c4-40df-40f3-9649-c2b3e4a5ee29\scratchpad\revision_e0\capturas\`
- `login_375.png`, `login_1280.png`
- `login_error_375.png`, `login_error_1280.png`
- `login_foco_375.png`, `login_foco_1280.png`
- `panel_inicio_375.png`, `panel_inicio_1280.png`, `panel_menu_abierto_375.png`
- `usuarios_index_375.png`, `usuarios_index_1280.png`
- `usuarios_eliminados_375.png`, `usuarios_eliminados_1280.png`
- `usuarios_crear_375.png`, `usuarios_crear_1280.png`
- `usuarios_crear_errores_375.png`, `usuarios_crear_errores_1280.png`
- `roles_index_375.png`, `roles_index_1280.png`
- `roles_crear_375.png`, `roles_crear_1280.png`
- `roles_editar_375.png`, `roles_editar_1280.png`
- `modulo_pendiente_375.png`, `modulo_pendiente_1280.png`
- `error_404_375.png`, `error_404_1280.png`

## Correcciones del Desarrollador (2026-10-25)

Todos los hallazgos están corregidos. `php artisan test`: 57 tests en verde. Pint `--test`: passed.

| # | Severidad | Hallazgo | Estado | Corrección (archivo:línea) |
|---|---|---|---|---|
| 1 | Alta | Cuenta `Inactivo` con sesión o "Recordarme" | **Corregido** | Middleware `app/Http/Middleware/AsegurarCuentaActiva.php:15` (logout + invalidar + login con "Este usuario no está activo"), alias y grupo del panel en `bootstrap/app.php:21,32`. `darDeBaja` regenera `remember_token` y borra `sessions`: `app/Services/Usuarios/UsuarioService.php:83-88`. Tests `tests/Feature/Auth/LoginTest.php:142,155` y `tests/Feature/Administracion/UsuariosTest.php:173` |
| 2 | Media | Escalada a SUPERADMIN | **Corregido** | `UsuarioService.php:137` (`rolesAsignables` sin SUPERADMIN si el actor no lo es), `:153` y `:160` (validan asignar SUPERADMIN o gestionar a un SUPERADMIN); regla en `app/Http/Requests/Administracion/GuardarUsuarioRequest.php:47`. Test `UsuariosTest.php:129` |
| 3 | Media | Modificar usuario Alumno | **Corregido** (se excluyen) | Scope `app/Models/User.php` `scopeGestionablesDesdeUsuarios`; listado `app/DataTables/UsuariosDataTable.php:20`; `{usuario}` solo resuelve gestionables `routes/panel.php:16` (404 para Alumno). Test `UsuariosTest.php:159` |
| 4 | Media | RutasPanelTest incompleto | **Corregido** | `tests/Feature/Autorizacion/RutasPanelTest.php:45` (parámetros por factory, verbo real); `:66-116` invitado → login, sin permiso → 403, con permiso y SUPERADMIN → 200/302, para todas las rutas `panel.*` |
| 5 | Media | `aria-describedby` del error | **Corregido** | `app/Support/CampoFormulario.php:23`, usado en `resources/views/components/form/{input,select,date,textarea,file}.blade.php:9/9/9/5/6` |
| 6 | Media | Etiquetas de permisos sin tilde | **Corregido** | Etiquetas en `config/permisos.php:39-73` (acciones y entidades); `app/Support/Permisos.php:15`; nombre técnico como tooltip `resources/views/panel/administracion/roles/_form.blade.php:34` |
| 7 | Media | `old()` en checkboxes | **Corregido** | `resources/views/components/form/checkbox.blade.php:9` (`session()->hasOldInput()`) |
| 8 | Media | Test del seeder tautológico | **Corregido** | Matriz §2.2 literal en `tests/Feature/Seeders/RolesPermisosSeederTest.php:35` |
| 9 | Media | Rate limit por correo+IP | **Corregido** | `tests/Feature/Auth/LoginTest.php:122` (otro correo misma IP y mismo correo otra IP entran) |
| 10 | Baja | Dos estilos de confirmación | **Corregido** | Todas con SweetAlert (`confirmar=`): `resources/views/panel/administracion/usuarios/_acciones.blade.php:5,10,15` y `roles/_acciones.blade.php:8,13`; se quitaron los modales de los index |
| 11 | Baja | Cambio del propio rol / último SUPERADMIN | **Corregido** | `UsuarioService.php:56` ("No puede cambiar su propio rol."); la baja propia ya estaba bloqueada y solo un SUPERADMIN gestiona a otro SUPERADMIN (`:160`), así que nunca queda sin SUPERADMIN. Test `UsuariosTest.php:148` |
| 12 | Baja | `$fillable` de User | **Corregido** | `app/Models/User.php:45-48` sin `password`, `estado_cuenta`, `fecha_alta`; `forceFill` en `UsuarioService.php:32,83,96,125` y `database/seeders/AdminSeeder.php:31,34`. La auditoría ahora registra todos los atributos cambiados (`app/Models/Concerns/AuditaCambios.php`) |
| 13 | Baja | Disco local `serve` | **Corregido** | `config/filesystems.php:36` `'serve' => false` |
| 14 | Baja | Logo | **Corregido** | `config/adminlte.php:24-25` (logo propio `public/img/logo-las.svg` y texto con `LAS_INSTITUTO_NOMBRE`) |
| 15 | Baja | Scripts de composer | **Corregido** | `composer.json:49` (`db:seed` en `setup`); se quitó la creación de `database.sqlite` |
| 16 | Baja | Desvíos del plan no anotados | **Corregido** | Sección "Desvíos respecto del plan" en `docs/diferencias-tesis.md` |
