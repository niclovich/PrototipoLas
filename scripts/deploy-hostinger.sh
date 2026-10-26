#!/usr/bin/env bash
set -Eeuo pipefail

trap 'printf "deploy-hostinger: error en la línea %s\n" "$LINENO" >&2' ERR

DEPLOY_ROOT="${1:-}"
RELEASE_ID="${2:-}"

if [[ -z "$DEPLOY_ROOT" || -z "$RELEASE_ID" ]]; then
    printf 'Uso: %s <deploy-root> <release-id>\n' "$0" >&2
    exit 64
fi

if [[ "$DEPLOY_ROOT" != /*/domains/* || "$DEPLOY_ROOT" == "/" ]]; then
    printf 'DEPLOY_ROOT no tiene el formato esperado de Hostinger: %s\n' "$DEPLOY_ROOT" >&2
    exit 1
fi

if [[ ! "$RELEASE_ID" =~ ^[A-Za-z0-9._-]+$ ]]; then
    printf 'Identificador de release inválido\n' >&2
    exit 1
fi

RELEASE_ROOT="$DEPLOY_ROOT/.deployments/$RELEASE_ID"
SOURCE_LARAVEL="$RELEASE_ROOT/laravel"
SOURCE_PUBLIC="$RELEASE_ROOT/public"
LARAVEL_DIR="$DEPLOY_ROOT/laravel"
PUBLIC_DIR="$DEPLOY_ROOT/public_html"

for required in "$SOURCE_LARAVEL/composer.json" "$SOURCE_LARAVEL/artisan" "$SOURCE_PUBLIC/index.php" "$SOURCE_PUBLIC/.htaccess"; do
    if [[ ! -f "$required" ]]; then
        printf 'Falta un archivo requerido en el release: %s\n' "$required" >&2
        exit 1
    fi
done

mkdir -p -- "$LARAVEL_DIR" "$PUBLIC_DIR"

# La primera ejecución debe hacerse después de retirar/revisar el checkout
# antiguo de public_html. El bloqueo evita borrar código o archivos desconocidos.
for legacy in app artisan bootstrap composer.json config database resources routes vendor .git; do
    if [[ -e "$PUBLIC_DIR/$legacy" ]]; then
        printf 'public_html contiene la ruta heredada "%s"; revise y límpiela manualmente antes de continuar.\n' "$legacy" >&2
        exit 1
    fi
done

if [[ -e "$PUBLIC_DIR/storage" && ! -L "$PUBLIC_DIR/storage" ]]; then
    printf 'public_html/storage existe como directorio o archivo; no se sobrescribirá.\n' >&2
    exit 1
fi

if [[ ! -f "$LARAVEL_DIR/.env" ]]; then
    printf 'No existe %s; el workflow nunca crea ni publica el .env.\n' "$LARAVEL_DIR/.env" >&2
    exit 1
fi

if ! grep -Eq '^APP_KEY=.+$' "$LARAVEL_DIR/.env"; then
    printf 'APP_KEY no está configurada en el .env del servidor.\n' >&2
    exit 1
fi

rsync -a --delete \
    --exclude='.env' \
    --exclude='storage/' \
    "$SOURCE_LARAVEL/" "$LARAVEL_DIR/"

rsync -a --delete \
    --exclude='storage' \
    "$SOURCE_PUBLIC/" "$PUBLIC_DIR/"

if [[ ! -L "$PUBLIC_DIR/storage" ]]; then
    ln -s "$LARAVEL_DIR/storage/app/public" "$PUBLIC_DIR/storage"
fi

cd "$LARAVEL_DIR"
php artisan migrate --force
php artisan optimize:clear
php artisan optimize

printf 'Release %s desplegado correctamente en %s\n' "$RELEASE_ID" "$DEPLOY_ROOT"
