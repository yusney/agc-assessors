#!/bin/sh
set -e

# Crear/renovar el symlink public/storage en cada arranque del contenedor.
STORAGE_LINK="/var/www/html/public/storage"
STORAGE_TARGET="/var/www/html/storage/app/public"

if [ ! -L "$STORAGE_LINK" ] || [ ! -e "$STORAGE_LINK" ]; then
    rm -f "$STORAGE_LINK"
    ln -s "$STORAGE_TARGET" "$STORAGE_LINK"
    echo "🔗 Created symlink: $STORAGE_LINK -> $STORAGE_TARGET"
fi

cd /var/www/html

echo "📦 Running production setup..."

# 1. Limpiar y cachear componentes de Filament
php artisan filament:clear-cached-components --quiet 2>/dev/null || true
php artisan filament:cache-components --quiet 2>/dev/null || true

# 2. Generar permisos de Shield
php artisan shield:generate --all --panel=admin --ignore-existing-policies 2>/dev/null || true

# 3. Verificar que se crearon permisos
PERM_COUNT=$(php artisan tinker --execute="echo \Spatie\Permission\Models\Permission::count();" 2>/dev/null | tail -1)
echo "🛡️ Permissions count: $PERM_COUNT"

# 4. Seedear roles (solo si hay permisos)
if [ "$PERM_COUNT" != "0" ] && [ -n "$PERM_COUNT" ]; then
    php artisan db:seed --class=DatabaseSeeder --force 2>/dev/null || true
    echo "🌱 Database seed complete."
else
    echo "⚠️ WARNING: No permissions found. Admin may not be able to login."
fi

echo "✅ Entrypoint complete."
exit 0
