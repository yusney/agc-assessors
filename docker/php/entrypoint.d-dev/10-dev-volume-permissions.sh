#!/bin/sh
set -eu

if [ "$(id -u)" -ne 0 ]; then
    echo "Development volume permissions must be fixed as root." >&2
    exit 1
fi

for directory in \
    /var/www/html/vendor \
    /var/www/html/node_modules \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache; do
    mkdir -p "$directory"
    chown -R www-data:www-data "$directory"
done
