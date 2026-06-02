#!/bin/sh
set -e

# Startup script: safe to run on EVERY container boot.
# Does NOT mutate user data (content). Only handles infrastructure + auth.

STORAGE_LINK="/var/www/html/public/storage"
STORAGE_TARGET="/var/www/html/storage/app/public"

cd /var/www/html

echo "📦 Running production startup..."

# 1. Symlink public/storage
if [ ! -L "$STORAGE_LINK" ] || [ ! -e "$STORAGE_LINK" ]; then
    rm -f "$STORAGE_LINK"
    ln -s "$STORAGE_TARGET" "$STORAGE_LINK"
    echo "🔗 Created symlink: $STORAGE_LINK -> $STORAGE_TARGET"
fi

# 2. Cache Filament components (required before shield:generate)
php artisan filament:clear-cached-components --quiet 2>/dev/null || true
php artisan filament:cache-components --quiet 2>/dev/null || true

# 3. Generate Shield permissions (idempotent — ignores existing policies)
php artisan shield:generate --all --panel=admin --ignore-existing-policies 2>/dev/null || true

# 4. Verify permissions exist
PERM_COUNT=$(php artisan tinker --execute="echo \Spatie\Permission\Models\Permission::count();" 2>/dev/null | tail -1)
echo "🛡️ Permissions count: $PERM_COUNT"

if [ "$PERM_COUNT" = "0" ] || [ -z "$PERM_COUNT" ]; then
    echo "⚠️ WARNING: No permissions found. Admin may not be able to login."
fi

# 5. Seed auth data (idempotent — safe to run on every boot)
php artisan db:seed --class=DatabaseSeeder --force 2>/dev/null || true
echo "🔐 Auth seed complete."

# NOTE: Content seeding (menu items, pages, services, news, team members)
# is a ONE-TIME setup operation. Run it manually once after first deploy:
#   docker compose exec php php artisan db:seed --class=InitialContentSeeder

echo "✅ Entrypoint complete."
exit 0
