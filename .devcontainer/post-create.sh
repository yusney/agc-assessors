#!/bin/bash
set -e

GREEN="\033[0;32m"
YELLOW="\033[1;33m"
BLUE="\033[0;34m"
RED="\033[0;31m"
NC="\033[0m"

echo ""
echo -e "${BLUE}=========================================${NC}"
echo -e "${BLUE}  AGC Assessors — Dev Container Setup   ${NC}"
echo -e "${BLUE}=========================================${NC}"
echo ""

cd /var/www/html

# .env
if [ ! -f .env ]; then
    echo -e "${YELLOW}▸ Creando .env desde .env.example...${NC}"
    cp .env.example .env
fi

# APP_KEY
if grep -q "^APP_KEY=$" .env || ! grep -q "^APP_KEY=" .env; then
    echo -e "${YELLOW}▸ Generando APP_KEY...${NC}"
    php artisan key:generate
fi

# Composer
if [ ! -f vendor/autoload.php ]; then
    echo -e "${YELLOW}▸ Instalando dependencias PHP...${NC}"
    composer install --no-interaction --prefer-dist --optimize-autoloader
else
    echo -e "${GREEN}✓ vendor/ ya existe${NC}"
fi

# Node
if [ ! -d node_modules ] || [ -z "$(ls -A node_modules 2>/dev/null)" ]; then
    echo -e "${YELLOW}▸ Instalando dependencias Node...${NC}"
    npm install
else
    echo -e "${GREEN}✓ node_modules/ ya existe${NC}"
fi

# Directorios Laravel
echo -e "${YELLOW}▸ Creando directorios de storage...${NC}"
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/app/public
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Storage link
php artisan storage:link 2>/dev/null || true

# PostgreSQL + migraciones
echo -e "${YELLOW}▸ Verificando conexión a PostgreSQL...${NC}"
if php artisan db:show --json 2>/dev/null | grep -q "pgsql"; then
    echo -e "${GREEN}✓ PostgreSQL conectado${NC}"
    echo -e "${YELLOW}▸ Ejecutando migraciones...${NC}"
    php artisan migrate --force
    echo -e "${YELLOW}▸ Ejecutando seeders...${NC}"
    php artisan db:seed --force || true
else
    echo -e "${RED}⚠️  No se pudo conectar a PostgreSQL.${NC}"
    echo -e "${YELLOW}   Edita .env y configura: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD${NC}"
    echo -e "${YELLOW}   Luego ejecuta: php artisan migrate${NC}"
fi

echo ""
echo -e "${GREEN}✅ Setup completado!${NC}"
echo ""
echo -e "${BLUE}URLs:${NC}"
echo "  🌐 App:      http://localhost:8080"
echo "  🔧 Admin:    http://localhost:8080/admin"
echo "  ⚡ Vite:     http://localhost:5173"
echo "  📧 Mailpit:  http://localhost:8025"
echo "  💾 Redis:    localhost:6379"
echo "  🐘 Postgres: localhost:5432"
echo ""
echo -e "${BLUE}Comandos útiles:${NC}"
echo "  php artisan serve       — no necesario (nginx corre solo)"
echo "  npm run dev             — Vite HMR"
echo "  php artisan migrate     — migraciones"
echo "  php artisan tinker      — REPL"
echo "  php artisan filament:*  — comandos Filament"
echo ""
