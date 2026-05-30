#!/bin/bash
# Se ejecuta cada vez que el contenedor arranca (no solo al crear)
# Útil para tareas ligeras que no queremos repetir en post-create

cd /var/www/html

# Limpiar caches de config/rutas si hay cambios recientes
php artisan config:clear 2>/dev/null || true
php artisan route:clear  2>/dev/null || true

echo "▸ Dev container listo — http://localhost:8080"
