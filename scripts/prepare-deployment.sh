
#!/usr/bin/env bash
set -Eeuo pipefail

trap 'printf "prepare-deployment: error en la línea %s\n" "$LINENO" >&2' ERR

SOURCE_DIR="${1:-}"
PRIVATE_DIR="${2:-}"
PUBLIC_DIR="${3:-}"

# ============================================
# 1. Validar argumentos
# ============================================

if [[ -z "$SOURCE_DIR" || -z "$PRIVATE_DIR" || -z "$PUBLIC_DIR" ]]; then
    printf 'Uso: %s <source-dir> <private-output> <public-output>\n' "$0" >&2
    exit 64
fi

# Normalizar rutas absolutas
SOURCE_DIR="$(realpath "$SOURCE_DIR")"
PRIVATE_DIR="$(realpath -m "$PRIVATE_DIR")"
PUBLIC_DIR="$(realpath -m "$PUBLIC_DIR")"

if [[ ! -f "$SOURCE_DIR/composer.json" ||
      ! -f "$SOURCE_DIR/artisan" ||
      ! -f "$SOURCE_DIR/public/index.php" ]]; then
    echo "ERROR: La fuente no parece una aplicación Laravel válida." >&2
    exit 1
fi

# Evitar eliminar accidentalmente el proyecto fuente
# o usar directorios de salida inseguros.
if [[ "$PRIVATE_DIR" == "$PUBLIC_DIR" ||
      "$PRIVATE_DIR" == "$SOURCE_DIR" ||
      "$PUBLIC_DIR" == "$SOURCE_DIR" ||
      "$PRIVATE_DIR" == "/" ||
      "$PUBLIC_DIR" == "/" ||
      "$PRIVATE_DIR" == "$SOURCE_DIR/"* ||
      "$PUBLIC_DIR" == "$SOURCE_DIR/"* ||
      "$SOURCE_DIR" == "$PRIVATE_DIR/"* ||
      "$SOURCE_DIR" == "$PUBLIC_DIR/"* ||
      "$PRIVATE_DIR" == "$PUBLIC_DIR/"* ||
      "$PUBLIC_DIR" == "$PRIVATE_DIR/"* ]]; then
    echo "ERROR: Las rutas de origen y destino no son seguras." >&2
    exit 1
fi

# ============================================
# 2. Validar dependencias y Vite
# ============================================

if [[ ! -f "$SOURCE_DIR/vendor/autoload.php" ]]; then
    echo "ERROR: Faltan las dependencias de Composer." >&2
    exit 1
fi

if [[ ! -f "$SOURCE_DIR/public/build/manifest.json" ]]; then
    echo "ERROR: No se encontró public/build/manifest.json." >&2
    echo "Ejecutá npm ci && npm run build antes de preparar el release." >&2
    exit 1
fi

if [[ ! -f "$SOURCE_DIR/public/.htaccess" ]]; then
    echo "ERROR: Falta public/.htaccess." >&2
    exit 1
fi

if [[ -e "$SOURCE_DIR/public/hot" ]]; then
    echo "ERROR: Existe public/hot, correspondiente a Vite en desarrollo." >&2
    exit 1
fi

# ============================================
# 3. Preparar directorios del release
# ============================================

rm -rf -- "$PRIVATE_DIR" "$PUBLIC_DIR"

mkdir -p -- "$PRIVATE_DIR" "$PUBLIC_DIR"

# ============================================
# 4. Preparar Laravel privado
# ============================================

rsync -a \
    --exclude='.git/' \
    --exclude='.github/' \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude='node_modules/' \
    --exclude='storage/' \
    --exclude='public/hot' \
    "$SOURCE_DIR/" "$PRIVATE_DIR/"

# ============================================
# 5. Preparar archivos públicos
# ============================================

rsync -a \
    --exclude='index.php' \
    --exclude='storage' \
    --exclude='hot' \
    "$SOURCE_DIR/public/" "$PUBLIC_DIR/"

# ============================================
# 6. Adaptar index.php para Hostinger
# ============================================

sed \
    -e "s#__DIR__.'/../storage#__DIR__.'/../laravel/storage#g" \
    -e "s#__DIR__.'/../vendor#__DIR__.'/../laravel/vendor#g" \
    -e "s#__DIR__.'/../bootstrap#__DIR__.'/../laravel/bootstrap#g" \
    "$SOURCE_DIR/public/index.php" > "$PUBLIC_DIR/index.php"

# ============================================
# 7. Verificar rutas del index.php generado
# ============================================

if ! grep -Fq "__DIR__.'/../laravel/vendor/autoload.php'" "$PUBLIC_DIR/index.php"; then
    echo "ERROR: No se adaptó correctamente la ruta de vendor/autoload.php." >&2
    exit 1
fi

if ! grep -Fq "__DIR__.'/../laravel/bootstrap/app.php'" "$PUBLIC_DIR/index.php"; then
    echo "ERROR: No se adaptó correctamente la ruta de bootstrap/app.php." >&2
    exit 1
fi

if grep -Eq "__DIR__[[:space:]]*\.[[:space:]]*['\"]/\\.\\./(vendor|bootstrap|storage)" "$PUBLIC_DIR/index.php"; then
    echo "ERROR: index.php contiene referencias privadas sin adaptar." >&2
    exit 1
fi

# ============================================
# 8. Validaciones finales
# ============================================

for required in \
    "$PRIVATE_DIR/artisan" \
    "$PRIVATE_DIR/composer.json" \
    "$PRIVATE_DIR/vendor/autoload.php" \
    "$PUBLIC_DIR/index.php" \
    "$PUBLIC_DIR/.htaccess" \
    "$PUBLIC_DIR/build/manifest.json"; do

    if [[ ! -f "$required" ]]; then
        printf 'ERROR: Falta un archivo requerido: %s\n' "$required" >&2
        exit 1
    fi
done

php -l "$PUBLIC_DIR/index.php"

chmod 0644 "$PUBLIC_DIR/index.php"

echo "========================================"
echo "Release Laravel preparado correctamente"
echo "========================================"
echo "Código privado: $PRIVATE_DIR"
echo "Archivos públicos: $PUBLIC_DIR"
echo "Vite: compilado"
echo "index.php: validado"
echo "========================================"
