#!/bin/sh
set -e

cd /var/www/html

# CRITICAL: bootstrap/cache is a persisted Docker volume. If it contains
# stale packages.php / config.php from before new packages were installed,
# Laravel won't discover them and artisan commands will fail.
rm -f bootstrap/cache/*.php

# Rediscover all packages (regenerates packages.php and services.php)
php artisan package:discover --ansi

# Clear all Laravel caches safely now that the manifest is fresh
php artisan optimize:clear

# Crear/renovar symlink public/storage
php artisan storage:link --force

echo "✅ Bootstrap cache rebuilt, packages rediscovered, and storage linked."
exit 0
