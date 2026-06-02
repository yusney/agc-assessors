#!/bin/sh
set -e

cd /var/www/html

# Crear/renovar symlink public/storage
php artisan storage:link --force

# Limpiar caches de view y app
php artisan view:clear
php artisan cache:clear

echo "✅ Cache cleared and storage linked."
exit 0
