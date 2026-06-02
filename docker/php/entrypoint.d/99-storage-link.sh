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

# 1. Cachear componentes de Filament (necesario para que shield:generate descubra recursos)
echo "📦 Caching Filament components..."
php artisan filament:cache-components --quiet || true

# 2. Generar permisos para todos los recursos
echo "🛡️ Generating Shield permissions..."
php artisan shield:generate --all --panel=admin --ignore-existing-policies || true

# 3. Verificar que se generaron permisos antes de seedear
PERM_COUNT=$(php artisan tinker --execute="echo Spatie\Permission\Models\Permission::count();" 2>/dev/null | tail -1)
if [ "$PERM_COUNT" = "0" ] || [ -z "$PERM_COUNT" ]; then
    echo "⚠️ WARNING: No permissions generated. Attempting fallback..."
    # Fallback: generar con discover resources explícito
    php artisan shield:generate --all --panel=admin --ignore-existing-policies
fi

# 4. Seedear roles y asignar permisos (idempotent — no duplica)
echo "🌱 Seeding roles and permissions..."
php artisan db:seed --class=RolesAndPermissionsSeeder --force || true

echo "✅ Entrypoint complete."
exit 0
