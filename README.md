# Sistema LAS

Sistema de gestión académica y administrativa del Instituto Superior del Milagro (prototipo de la tesis LAS).
Laravel 12 + MySQL/MariaDB + AdminLTE 3. Casos de uso principales: CU1 Preinscripción, CU2 Inscripción,
CU3 Inscripción en Comisiones (y Cambio de Comisión), CU4 Regularidad Académica, CU5 Inscripción a Exámenes,
CU6 Asentamiento de Acta y CU7 Pago de Cuotas (con Apertura y Cierre de Caja), más los CU de soporte y el
módulo de Auditoría.

Estado del desarrollo por etapa: [docs/ESTADO.md](docs/ESTADO.md).

## Requisitos

- PHP 8.2 o superior con las extensiones `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip` e `intl`.
- Composer 2.
- Node.js 20 o superior y npm (solo para compilar el CSS/JS propio con Vite).
- MySQL 8 o MariaDB 10.4+ (en desarrollo, el de XAMPP).

## Instalación

```bash
composer install
npm ci && npm run build
cp .env.example .env
php artisan key:generate
```

Configurar en `.env` al menos:

- `APP_URL` (en local, `http://sistema-las.localhost:8000`).
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (por ejemplo, base `sistema_las`).
- `MAIL_MAILER=log` en desarrollo (o los datos de Mailtrap/SMTP).
- `ADMIN_EMAIL` y `ADMIN_PASSWORD` del superadministrador inicial (obligatorio para el seeder).
- Los usuarios y datos de demostración se crean al ejecutar el seeder.

Luego crear las tablas, los roles y permisos, los catálogos, el administrador y (en `local`) los datos demo:

```bash
php artisan migrate --seed
php artisan storage:link
```

Para volver a empezar desde cero: `php artisan migrate:fresh --seed`.

## Ejecución

```bash
php artisan serve            # o el servidor de XAMPP apuntando a public/
php artisan queue:work       # correos al alumno (CU2–CU6) y notificaciones: van por la cola "database"
```

`composer dev` levanta servidor, cola, logs y Vite juntos.

Acceda siempre con `http://sistema-las.localhost:8000` (no con `http://localhost/...`) para que el navegador no mezcle
las credenciales guardadas con las del sistema anterior.

## Acceso y usuarios de demostración

El inicio de sesión es con **correo electrónico y contraseña** (el DNI no se usa para ingresar).
Los usuarios demo los crea `DemoSeeder` al ejecutar el seeder. Contraseña de todos: `Demo1234`.

| Rol | Nombre | Correo electrónico | Para probar |
| --- | --- | --- | --- |
| Director General | Laura Fernández | `director@las.test` | Personal, Cuotas por Carrera |
| Coordinador | Martín Gómez | `coordinador@las.test` | Carreras, Materias, Planes, Correlatividades, Comisiones, Actas, Cuotas |
| Preceptor | Carla López | `preceptor@las.test` | CU1–CU6 (Gestión de Alumnos, Regularidad, Asentamiento del acta 0001) |
| Auxiliar Administrativo | Diego Martínez | `auxiliar@las.test` | CU1, CU2 |
| Docente | Sofía Rodríguez | `docente@las.test` | CU4 Regularidad |
| Cajero | Pablo Sánchez | `cajero@las.test` | CU7 Pago de Cuotas, Cajas (tiene una caja Abierta) |
| Alumno | Juan Pérez | `alumno@las.test` | CU5: al día, Regular en Programación I y Matemática, inscripto al acta 0001 |
| Alumno | Lucía Torres | `moroso@las.test` | Bloqueos por cuotas vencidas (CU3, CU5) |

`preinscripto@las.test` (Ana Ramírez) no tiene contraseña: sirve para CU2 (Inscripción) y para pagar la cuota de Inscripción.

El **superadministrador** (`ADMIN_EMAIL` / `ADMIN_PASSWORD` del `.env`) tiene todos los permisos, incluido el módulo
**Auditoría** (Administración → Auditoría). Estas credenciales son solo para desarrollo; no usarlas en producción.

## Tests

Los tests usan una base aparte (`sistema_las_test`, ver `phpunit.xml`), que debe existir y estar vacía:

```bash
php artisan test                         # suite completa
php artisan test --filter=InscripcionExamenTest
vendor/bin/pint --test                   # estilo de código
```

## Documentación

- [docs/ESTADO.md](docs/ESTADO.md): estado de cada etapa y entorno local.
- [docs/planes/](docs/planes/): plan general (modelo, permisos, decisiones) y plan de cada etapa.
- [docs/diferencias-tesis.md](docs/diferencias-tesis.md) y [docs/trazabilidad.md](docs/trazabilidad.md): diferencias con la tesis y atributo → columna.
- [docs/deployment-hostinger.md](docs/deployment-hostinger.md): despliegue en Hostinger con GitHub Actions
  (el código Laravel queda en `laravel/`, fuera de `public_html/`; secretos, preparación inicial, validación y rollback).
