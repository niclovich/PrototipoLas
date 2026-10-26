#!/usr/bin/env bash
set -Eeuo pipefail

trap 'printf "prepare-deployment: error en la línea %s\n" "$LINENO" >&2' ERR

SOURCE_DIR="${1:-}"
PRIVATE_DIR="${2:-}"
PUBLIC_DIR="${3:-}"

if [[ -z "$SOURCE_DIR" || -z "$PRIVATE_DIR" || -z "$PUBLIC_DIR" ]]; then
    printf 'Uso: %s <source-dir> <private-output> <public-output>\n' "$0" >&2
    exit 64
fi

if [[ ! -f "$SOURCE_DIR/composer.json" || ! -f "$SOURCE_DIR/public/index.php" ]]; then
    printf 'La fuente no parece una aplicación Laravel válida: %s\n' "$SOURCE_DIR" >&2
    exit 1
fi

rm -rf -- "$PRIVATE_DIR" "$PUBLIC_DIR"
mkdir -p -- "$PRIVATE_DIR" "$PUBLIC_DIR"

rsync -a \
    --exclude='.git/' \
    --exclude='.env' \
    --include='.env.example' \
    --exclude='.env.*' \
    --exclude='node_modules/' \
    --exclude='storage/' \
    "$SOURCE_DIR/" "$PRIVATE_DIR/"

rsync -a \
    --exclude='index.php' \
    --exclude='storage' \
    "$SOURCE_DIR/public/" "$PUBLIC_DIR/"

sed \
    -e "s#__DIR__.'/../storage#__DIR__.'/../laravel/storage#g" \
    -e "s#__DIR__.'/../vendor#__DIR__.'/../laravel/vendor#g" \
    -e "s#__DIR__.'/../bootstrap#__DIR__.'/../laravel/bootstrap#g" \
    "$SOURCE_DIR/public/index.php" > "$PUBLIC_DIR/index.php"

chmod 0644 "$PUBLIC_DIR/index.php"
