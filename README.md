# Sistema LAS

Sistema de gestión académica y administrativa del Instituto Superior del Milagro.

## Acceso

El inicio de sesión se realiza con **correo electrónico y contraseña**. El DNI no se utiliza para ingresar.

Para que el navegador identifique este proyecto como un sitio independiente del sistema anterior, acceda siempre mediante:

```text
http://sistema-las.localhost:8000
```

Puede iniciar el entorno local completo con:

```bash
composer dev
```

No utilice la antigua dirección basada en `http://localhost/...`, porque los gestores de contraseñas agrupan las credenciales guardadas por sitio y podrían volver a mostrar las del sistema anterior.

### Usuarios de demostración

Estos usuarios se crean mediante `DemoSeeder` cuando la aplicación se ejecuta con `APP_ENV=local`.

| Rol | Nombre | Correo electrónico | DNI | Contraseña |
| --- | --- | --- | --- | --- |
| Director General | Laura Fernández | `director@las.test` | `20111111` | `Demo1234` |
| Coordinador | Martín Gómez | `coordinador@las.test` | `20222222` | `Demo1234` |
| Preceptor | Carla López | `preceptor@las.test` | `20333333` | `Demo1234` |
| Auxiliar Administrativo | Diego Martínez | `auxiliar@las.test` | `20444444` | `Demo1234` |
| Docente | Sofía Rodríguez | `docente@las.test` | `20555555` | `Demo1234` |
| Cajero | Pablo Sánchez | `cajero@las.test` | `20666666` | `Demo1234` |
| Alumno | Juan Pérez | `alumno@las.test` | `40777777` | `Demo1234` |

> Estas credenciales son únicamente para desarrollo y demostración. No deben utilizarse en producción.

### Superadministrador

La cuenta de superadministrador se configura en el archivo `.env` antes de ejecutar los seeders:

```dotenv
ADMIN_NOMBRE=Administrador
ADMIN_APELLIDO=Sistema
ADMIN_DNI=00000000
ADMIN_EMAIL=admin@las.test
ADMIN_PASSWORD=UnaClaveSegura123
```

`ADMIN_PASSWORD` es obligatorio. La contraseña real es la que se haya definido en el entorno y no debe publicarse en este archivo.

### Usuario preinscripto

El seeder también crea `preinscripto@las.test` (Ana Ramírez, DNI `40888888`). Esta cuenta todavía no tiene contraseña definida y, por lo tanto, no puede iniciar sesión hasta completar el proceso de alta.

## Crear los usuarios

Para preparar la base de datos desde cero con los usuarios y datos de demostración:

```bash
php artisan migrate:fresh --seed
```

Para ejecutar únicamente los datos de demostración sobre una base ya preparada:

```bash
php artisan db:seed --class=DemoSeeder
```

El `DemoSeeder` requiere que previamente existan los roles, permisos y catálogos del sistema.

## Despliegue en Hostinger

El despliegue de producción se ejecuta manualmente desde GitHub Actions y mantiene
el código Laravel en `laravel/`, fuera de `public_html/`. La configuración completa
de secretos, la preparación inicial de Hostinger, la validación y el rollback está
documentada en [docs/deployment-hostinger.md](docs/deployment-hostinger.md).
