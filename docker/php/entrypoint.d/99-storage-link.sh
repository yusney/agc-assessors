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

# Generar permisos de Filament Shield y seedear roles.
# Esto es crítico para producción donde los permisos deben existir
# antes de que los usuarios puedan acceder al panel.
cd /var/www/html

# 1. Generar permisos para todos los recursos (idempotent — no duplica)
php artisan shield:generate --all --panel=admin --ignore-existing-policies --quiet || true

# 2. Seedear roles y asignar permisos (idempotent — no duplica)
#    Crea: super_admin, manager, editor, viewer
#    Asigna: permisos a cada rol
#    Asigna: super_admin al usuario admin@agcassessors.com
php artisan db:seed --class=RolesAndPermissionsSeeder --force --quiet || true

echo "✅ Entrypoint complete."
exit 0
