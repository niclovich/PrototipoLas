# Despliegue de producción en Hostinger

Este proyecto usa GitHub Actions para construir PHP y Vite en GitHub. Hostinger
solo recibe un release ya construido por SSH; no se usa el despliegue Git
integrado de Hostinger.

## Resultado en el servidor

Para `HOSTINGER_DEPLOY_ROOT=/home/u163739185/domains/mediumvioletred-bee-491616.hostingersite.com`:

```text
laravel/       código, vendor y bootstrap; contiene el .env privado
public_html/   index.php, .htaccess y los assets públicos
.deployments/  releases construidos, útiles para rollback
```

`.builds/` y `DO_NOT_UPLOAD_HERE` son ajenos al workflow y no se modifican.

## Secrets de GitHub

Configurar estos secrets en **Settings > Secrets and variables > Actions** (idealmente
en el environment `production`):

| Secret | Valor |
| --- | --- |
| `HOSTINGER_HOST` | Host SSH entregado por Hostinger |
| `HOSTINGER_PORT` | `65002` |
| `HOSTINGER_USERNAME` | Usuario SSH, por ejemplo `u163739185` |
| `HOSTINGER_SSH_PRIVATE_KEY` | Clave privada de una clave dedicada, sin passphrase interactiva |
| `HOSTINGER_KNOWN_HOSTS` | Línea completa de `known_hosts` para el host y puerto SSH |
| `HOSTINGER_DEPLOY_ROOT` | `/home/u163739185/domains/mediumvioletred-bee-491616.hostingersite.com` |

Generar `known_hosts` desde una red confiable y revisar la huella antes de guardarlo:

```bash
ssh-keyscan -p 65002 HOSTINGER_HOST
```

No usar `StrictHostKeyChecking=no` ni guardar la clave privada en el repositorio.

## Preparación única en Hostinger

1. Desactivar el despliegue Git automático de Hostinger para este dominio. Si se
   mantiene activo, volverá a copiar el repositorio completo en `public_html`.
2. Crear o conservar `laravel/` y configurar allí el `.env` de producción. Nunca
   copiar el `.env` desde GitHub.
3. Seleccionar PHP 8.2 en hPanel y confirmar por SSH que el PHP de CLI también sea
   8.2 (`php -v`). Deben estar disponibles `bcmath`, `ctype`, `curl`, `dom`,
   `exif`, `fileinfo`, `gd`, `intl`, `mbstring`, `pdo_mysql`, `soap`, `xml` y
   `zip`; si el CLI usa otra versión, ajustar el PATH o la configuración PHP de
   Hostinger antes del primer despliegue.
4. Completar al menos `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`,
   `DB_*`, `ADMIN_*` y los parámetros de correo. No ejecutar `key:generate` sobre
   una instalación existente.
5. Crear la base MySQL y el usuario desde hPanel. El nombre real puede llevar el
   prefijo de la cuenta; usar esos valores, no los ejemplos locales.
6. Revisar y retirar manualmente el checkout anterior de `public_html`, conservando
   solo archivos que se hayan identificado como necesarios. La primera ejecución
   se detiene si encuentra `app/`, `artisan`, `vendor/`, `.git/` u otro indicador
   del repositorio allí.
7. Confirmar que no existe un `public_html/storage` real con archivos de usuarios.
   El script crea un enlace simbólico a `laravel/storage/app/public`. Si Hostinger
   no permite symlinks, detenerse y resolverlo con soporte antes de desplegar.

El workflow no elimina la base de datos, no ejecuta `migrate:fresh`, `db:wipe` ni
seeders, y conserva `laravel/.env` y `laravel/storage/` durante las sincronizaciones.

## Primer despliegue

1. Hacer una copia de seguridad de la base de datos desde hPanel y conservarla.
2. Verificar los secrets y la huella SSH.
3. En **Actions > Deploy Laravel to Hostinger**, ejecutar `Run workflow` con `dev`
   (o el ref que haya sido revisado).
4. Si el job se detiene por archivos heredados, revisar `public_html` por SSH,
   eliminar únicamente el checkout antiguo identificado y volver a ejecutar.
5. Verificar por HTTPS la página de login, un recurso de `build/`, un recurso de
   `img/`, el login y una URL bajo `storage/` si existen archivos públicos.
6. Revisar `laravel/storage/logs/laravel.log` y el resultado de `php artisan migrate:status`.

Las migraciones se ejecutan con `--force`; son incrementales y no destructivas.
`php artisan optimize:clear` y `php artisan optimize` regeneran cachés de producción.

## Actualizaciones posteriores

Por ahora el workflow es manual para evitar publicar un push accidental. El flujo
es `Actions > Run workflow > ref=dev`. Cada ejecución:

1. instala PHP 8.2 y las extensiones requeridas;
2. ejecuta `composer install --no-dev` usando `composer.lock`;
3. ejecuta `npm ci` y `npm run build` en GitHub;
4. sube el release a `.deployments/<commit>/`;
5. sincroniza el código privado y solo los archivos de `public/`;
6. preserva `.env`, `storage/` y la base de datos;
7. ejecuta migraciones seguras y reconstruye las cachés.

El `index.php` público se genera para cargar `../laravel/vendor` y
`../laravel/bootstrap`; no se modifica el `public/index.php` fuente.

## Rollback

No borrar el release que se desea recuperar. Desde SSH, revisar los releases:

```bash
ls -1t /home/u163739185/domains/mediumvioletred-bee-491616.hostingersite.com/.deployments
```

Para activar uno anterior, volver a ejecutar el workflow con el commit/ref
correspondiente si todavía está disponible. Antes de hacerlo, hacer backup de la
base de datos y comprobar si ese release contiene migraciones incompatibles.
El rollback del código no revierte migraciones automáticamente: restaurar la base
solo con un backup verificado y con una ventana de mantenimiento.

## Comprobaciones locales

Desde `sistema-las/`:

```bash
composer validate --strict
composer check-platform-reqs
npm ci
npm run build
php artisan test
```

`composer setup`, `migrate:fresh` y `db:wipe` son comandos de desarrollo y no
forman parte del despliegue.
