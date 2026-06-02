#!/bin/sh
set -e

# Crear/renovar el symlink public/storage en cada arranque del contenedor.
# Necesario porque storage/app/public se monta como volumen en runtime,
# por lo que un symlink creado durante el build se rompe cuando el volumen se monta.

STORAGE_LINK="/var/www/html/public/storage"
STORAGE_TARGET="/var/www/html/storage/app/public"

if [ ! -L "$STORAGE_LINK" ] || [ ! -e "$STORAGE_LINK" ]; then
    rm -f "$STORAGE_LINK"
    ln -s "$STORAGE_TARGET" "$STORAGE_LINK"
    echo "🔗 Created symlink: $STORAGE_LINK -> $STORAGE_TARGET"
fi

exit 0
