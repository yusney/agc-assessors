# AGC Assessors - Docker Development Environment

Entorno de desarrollo completo para AGC Assessors basado en Docker, con soporte para Laravel 13, Filament 5, PHP 8.4, PostgreSQL 16, Node 24 LTS, Tailwind CSS 4 y Vite.

## Servicios Incluidos

| Servicio | Imagen | Puerto | Descripción |
|----------|--------|--------|-------------|
| **PHP-FPM** | `php:8.4-fpm-alpine` (custom) | 9000 | PHP 8.4 con todas las extensiones para Laravel + Xdebug |
| **Nginx** | `nginx:alpine` | 8080 | Servidor web con configuración optimizada para Laravel |
| **PostgreSQL** | `postgres:16-alpine` (external) | 5432 | Base de datos principal (servidor externo) |
| **Node** | `node:24-alpine` | 5173 | Vite HMR para desarrollo frontend |
| **Redis** | `redis:7-alpine` | 6379 | Caché, sesiones y colas |
| **Mailpit** | `axllent/mailpit:latest` | 8025 / 1025 | Captura de emails en desarrollo (UI + SMTP) |
| **Queue** | `php:8.4-fpm-alpine` (custom) | - | Worker de colas (opcional, `make queue-up`) |
| **Scheduler** | `php:8.4-fpm-alpine` (custom) | - | Cron de Laravel (opcional, `make scheduler-up`) |

## Extensiones PHP Instaladas

- **Laravel 13 Core:** ctype, curl, dom, fileinfo, filter, hash, intl, mbstring, opcache, pdo, pdo_pgsql, pgsql, session, tokenizer, xml, zip
- **Filament / Media:** gd, imagick (para Spatie Media Library)
- **Utilidades:** bcmath, exif, redis, simplexml, sockets, xsl
- **Desarrollo:** xdebug (debug remoto para VSCode/PhpStorm)

## Primeros Pasos

### 1. Clonar y Entrar al Directorio

```bash
cd /home/yusney/app/agc
```

### 2. Ejecutar Setup Automático

```bash
./docker/setup.sh
```

Este script hará todo automáticamente:
- Verificar que Docker esté instalado
- Crear `.env` desde `.env.example`
- Crear directorios necesarios
- Construir imágenes Docker
- Iniciar contenedores
- Instalar dependencias PHP (Composer) y Node (npm)
- Generar APP_KEY
- Ejecutar migraciones y seeders
- Crear enlace simbólico de storage

### 3. Acceder a la Aplicación

| Servicio | URL |
|----------|-----|
| Aplicación Web | http://localhost:8080 |
| Panel Filament | http://localhost:8080/admin |
| Vite HMR | http://localhost:5173 |
| Mailpit (emails) | http://localhost:8025 |

### 4. Crear Usuario Admin de Filament

```bash
make filament-user
```

## Comandos Útiles (Makefile)

```bash
# Ver todos los comandos disponibles
make help

# Ciclo de vida de Docker
make build          # Reconstruir imágenes
make up             # Iniciar contenedores
make down           # Detener contenedores
make restart        # Reiniciar contenedores
make fresh          # Destruir todo y reconstruir desde cero

# Shell / Acceso a contenedores
make shell          # Entrar al contenedor PHP (bash)
make shell-node     # Entrar al contenedor Node (sh)
make shell-db       # Consola PostgreSQL (psql)

# Composer
make composer-install
make composer-update
make composer ARGS="require laravel/sanctum"

# Artisan
make artisan ARGS="migrate"
make artisan ARGS="db:seed --class=OfficeSeeder"
make migrate
make migrate-fresh
make seed
make artisan-key

# Filament
make filament-optimize
make filament-user

# Node / Vite
make npm-install
make npm ARGS="run build"
make dev            # Iniciar Vite dev server
make build-assets   # Build de assets para producción

# Testing y Calidad
make test           # Ejecutar tests (Pest/PHPUnit)
make test-coverage  # Tests con cobertura
make phpstan        # Análisis estático PHPStan nivel 8
make pint           # Formatear código con Laravel Pint
make pint-dry       # Verificar formato sin modificar

# Mantenimiento
make clear          # Limpiar todas las cachés
make optimize       # Optimizar Laravel
make optimize-clear # Limpiar optimizaciones
make logs           # Ver logs de todos los contenedores
make logs-php       # Ver logs del contenedor PHP

# Colas y Scheduler (perfilados)
make queue-up       # Iniciar worker de colas
make scheduler-up   # Iniciar scheduler

# Base de datos
make db-export      # Exportar BD a backup.sql
make db-import      # Importar BD desde backup.sql

# Información
make status         # Estado de los contenedores
make ports          # Mostrar puertos expuestos
```

## Estructura de Docker

```
docker/
├── php/
│   ├── Dockerfile          # PHP 8.4-FPM con extensiones
│   └── php.ini            # Configuración PHP personalizada
├── nginx/
│   ├── nginx.conf          # Configuración principal Nginx
│   └── sites/
│       └── default.conf    # Virtual host para Laravel
└── setup.sh               # Script de configuración automática

> **Nota:** Este proyecto usa un **servidor PostgreSQL externo**. 
> El contenedor de base de datos NO está incluido en este docker-compose.
> Configura `DB_HOST` en tu `.env` para apuntar a tu servidor PostgreSQL.
```

## Configuración de Xdebug

El contenedor PHP incluye Xdebug 3 configurado para desarrollo remoto:

- **Puerto:** 9003
- **IDE Key:** AGC
- **Modo:** debug, develop
- **Client Host:** host.docker.internal (auto-detect)

### VSCode
Instala la extensión "PHP Debug" y crea `.vscode/launch.json`:

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug (Docker)",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            }
        }
    ]
}
```

### PhpStorm
1. Settings → PHP → Servers: Add server
   - Name: `agc-docker`
   - Host: `localhost`
   - Port: `8080`
   - Path mappings: `/home/yusney/app/agc` → `/var/www/html`
2. Settings → PHP → Debug → Xdebug: Port 9003
3. Start listening for debug connections

## Variables de Entorno Importantes

| Variable | Descripción | Default |
|----------|-------------|---------|
| `APP_PORT` | Puerto de la aplicación | 8080 |
| `DB_PORT` | Puerto PostgreSQL | 5432 |
| `DB_DATABASE` | Nombre de la BD | agc |
| `DB_USERNAME` | Usuario BD | agc |
| `DB_PASSWORD` | Password BD | secret |
| `VITE_PORT` | Puerto Vite HMR | 5173 |
| `MAILPIT_UI_PORT` | Puerto UI Mailpit | 8025 |
| `MAILPIT_SMTP_PORT` | Puerto SMTP Mailpit | 1025 |
| `XDEBUG_MODE` | Modo Xdebug | debug |

## Resolución de Problemas

### Permisos de Storage
```bash
make shell
chmod -R 775 storage bootstrap/cache
chown -R agc:agc storage bootstrap/cache
```

### Vite no detecta cambios (Hot Module Replacement)
El contenedor Node tiene `CHOKIDAR_USEPOLLING=true`. Si sigue sin funcionar:
```bash
make restart
```

### Base de datos no responde (PostgreSQL externo)
```bash
# Verifica que tu servidor PostgreSQL esté corriendo
docker ps | grep postgres

# Verifica la conexión desde el contenedor PHP
docker compose exec php php artisan db:monitor

# Verifica las variables de entorno
cat .env | grep DB_
```

### Errores de Composer
```bash
make composer ARGS="clear-cache"
make composer ARGS="install --no-cache"
```

### Limpiar todo y empezar de cero
```bash
make fresh
make install
```

## Producción vs Desarrollo

Este entorno está **optimizado para desarrollo**. Para producción:

1. Deshabilitar Xdebug: `XDEBUG_MODE=off`
2. Habilitar OPcache con `opcache.validate_timestamps=0`
3. Usar `php artisan optimize` en el deployment
4. Usar `php artisan filament:optimize`
5. Configurar un disk de almacenamiento de producción (S3, etc.)
6. Usar Redis para cache/sessions en producción
7. Configurar certificados SSL en Nginx

## Licencia

Proyecto privado para AGC Assessors. Todos los derechos reservados.
