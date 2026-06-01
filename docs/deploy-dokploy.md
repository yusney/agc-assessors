# Despliegue en Dokploy — AGC Assessors

Guía completa para desplegar AGC Assessors en producción usando Dokploy con imagen Docker pre-compilada desde GitHub Container Registry (GHCR).

---

## Arquitectura

```
GitHub (push a master)
    ↓
GitHub Actions CI
    ↓ build + push
GHCR (ghcr.io/yusney/agc-assessors:latest)
    ↓ pull
Dokploy (VPS)
    ↓
Traefik → contenedor PHP+Nginx (puerto 8080)
```

**Stack en producción:**
- PHP 8.4 + Nginx (imagen `serversideup/php:8.4-fpm-nginx`)
- PostgreSQL gestionado por Dokploy
- Sin Redis, sin Queue Worker, sin Scheduler
- Mails síncronos (sin ShouldQueue)

---

## Requisitos previos

- VPS con Dokploy instalado
- Repositorio en GitHub con acceso a GHCR
- Dominio apuntando al VPS (registro A o CNAME)

---

## Paso 1 — Configurar GitHub Actions (CI)

El archivo `.github/workflows/docker-build.yml` buildea y pushea la imagen a GHCR en cada push a `master`.

Variables necesarias en **GitHub → Settings → Secrets and variables → Actions**:

| Secret | Descripción |
|--------|-------------|
| `GHCR_TOKEN` | Personal Access Token con permisos `write:packages` |

La imagen resultante: `ghcr.io/yusney/agc-assessors:latest`

---

## Paso 2 — Crear la aplicación en Dokploy

1. Dokploy → **Create Application**
2. **Type**: Docker Image
3. **Image**: `ghcr.io/yusney/agc-assessors:latest`
4. **Registry**: GitHub Container Registry
   - Username: `yusney`
   - Token: Personal Access Token con `read:packages`

---

## Paso 3 — Variables de entorno

En Dokploy → tu app → **Environment**, configurar:

```env
APP_NAME="AGC Assessors"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://agc.donduque.dev

# Base de datos (usar los valores que genera Dokploy al crear la DB)
DB_CONNECTION=pgsql
DB_HOST=
DB_PORT=5432
DB_DATABASE=agc
DB_USERNAME=
DB_PASSWORD=

# Sesiones y cache (sin Redis)
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Proxies (CRÍTICO — sin esto los assets cargan en HTTP en lugar de HTTPS)
TRUSTED_PROXIES=*

# Laravel App Key (generar con: php artisan key:generate --show)
APP_KEY=base64:...

# Curator (gestor de medios)
CURATOR_GLIDE_TOKEN=f7XFY7wJBASjyQiw9OBUmwwolxTOcFsT

# Mail (configurar proveedor real)
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@agcassessors.com
MAIL_FROM_NAME="AGC Assessors"
```

> **IMPORTANTE**: `TRUSTED_PROXIES=*` es obligatorio. Sin esto, Laravel genera URLs HTTP en lugar de HTTPS porque no detecta que está detrás de Traefik.

---

## Paso 4 — Configurar el puerto

En Dokploy → tu app → **General**:

- **Port**: `8080`

---

## Paso 5 — Configurar el Entrypoint

En Dokploy → tu app → **Advanced**:

- **Command / Entrypoint**:
```
/bin/sh -c "cd /var/www/html && php artisan storage:link --force && /init"
```

Esto ejecuta `storage:link` en cada arranque del contenedor para que las imágenes de la biblioteca de medios sean accesibles desde el frontend.

---

## Paso 6 — Configurar volúmenes persistentes

En Dokploy → tu app → **Volumes / Mounts** → **Add Volume**:

Usar **Volume Mount** (no Bind Mount) para cada uno:

| Volume Name | Mount Path (en el contenedor) |
|-------------|-------------------------------|
| `agc-storage` | `/var/www/html/storage` |
| `agc-cache` | `/var/www/html/bootstrap/cache` |

> **CRÍTICO**: Sin estos volúmenes, todas las imágenes subidas desde el panel de admin se pierden en cada redeploy.

---

## Paso 7 — Configurar el dominio

En Dokploy → tu app → **Domains**:

- **Domain**: `agc.donduque.dev`
- **HTTPS**: activar (Traefik gestiona el certificado Let's Encrypt automáticamente)
- **Port**: `8080`

---

## Paso 8 — Crear la base de datos

En Dokploy → **Databases** → **Create Database**:

- **Type**: PostgreSQL
- **Name**: `agc`

Dokploy genera las credenciales automáticamente. Copiarlas a las variables de entorno del Paso 3.

---

## Paso 9 — Primer deploy

1. Click en **Deploy** en Dokploy
2. Esperar que Dokploy pull la imagen de GHCR y arranque el contenedor
3. Verificar en **Logs** que el contenedor arrancó sin errores

---

## Paso 10 — Inicializar la base de datos

Una vez el contenedor esté corriendo, ejecutar via **Terminal** en Dokploy o SSH:

```bash
docker exec -it $(docker ps -q -f name=agc) sh -c "cd /var/www/html && php artisan migrate --force"
```

Luego correr los seeders:

```bash
docker exec -it $(docker ps -q -f name=agc) sh -c "cd /var/www/html && php artisan db:seed --force"
```

---

## Paso 11 — Crear usuario admin

```bash
docker exec -it $(docker ps -q -f name=agc) sh -c "cd /var/www/html && php artisan tinker --execute=\"
\\App\\Models\\User::create([
    'name' => 'Admin',
    'email' => 'admin@agcassessors.com',
    'password' => bcrypt('Admin*123'),
    'email_verified_at' => now(),
]);
\""
```

---

## Paso 12 — Restaurar backup de base de datos (opcional)

Si tenés un backup local (`agc_backup_local.sql`), podés restaurarlo usando DBeaver conectado a la base de datos remota:

1. Abrir DBeaver → conexión remota → base de datos `agc`
2. Click derecho → **Tools → Execute Script**
3. Seleccionar `agc_backup_local.sql`
4. Ejecutar

O via terminal:

```bash
# Subir el backup al VPS
scp agc_backup_local.sql root@IP_VPS:/root/

# Copiar al contenedor de PostgreSQL
docker cp /root/agc_backup_local.sql $(docker ps -q -f name=postgres):/tmp/

# Restaurar
docker exec -it $(docker ps -q -f name=postgres) bash
psql -U $POSTGRES_USER -d agc < /tmp/agc_backup_local.sql
```

---

## Deploys automáticos (CD)

Cada push a `master`:

1. GitHub Actions buildea la nueva imagen y la pushea a GHCR (~1-2 min)
2. En Dokploy hacer **Redeploy** para que tome la nueva imagen

Para automatizar el redeploy, Dokploy soporta **Webhooks**. Configurar en Dokploy → tu app → **Webhooks** y agregar la URL en GitHub → Settings → Webhooks.

---

## Verificación post-deploy

```bash
# Estado del contenedor
docker ps | grep agc

# Logs del contenedor
docker logs $(docker ps -q -f name=agc) --tail 50

# Smoke test HTTP
curl -s -o /dev/null -w "%{http_code}" https://agc.donduque.dev/

# Verificar storage:link
docker exec -it $(docker ps -q -f name=agc) sh -c "ls -la /var/www/html/public/storage"
```

---

## Troubleshooting

### 403 Forbidden en el panel admin
El modelo `User` debe implementar `FilamentUser` con `canAccessPanel()` retornando `true`. Ver `app/Models/User.php`.

### Assets cargando en HTTP (mixed content)
Verificar que `TRUSTED_PROXIES=*` está configurado en las variables de entorno. Sin esto, Laravel no detecta que está detrás de Traefik y genera URLs HTTP.

### Imágenes de la biblioteca no se ven en el frontend
1. Verificar que el volumen `agc-storage` está montado en `/var/www/html/storage`
2. Verificar que el entrypoint ejecuta `php artisan storage:link --force`
3. Ejecutar manualmente: `docker exec -it $(docker ps -q -f name=agc) sh -c "cd /var/www/html && php artisan storage:link --force"`

### Error `LocaleViewPath` al arrancar
El alias en `bootstrap/app.php` debe ser `LaravelLocalizationViewPath`, no `LocaleViewPath`.

### Permisos de storage
```bash
docker exec -it $(docker ps -q -f name=agc) sh -c "chown -R www-data:www-data /var/www/html/storage"
```

---

## URLs importantes

| Recurso | URL |
|---------|-----|
| Sitio público | https://agc.donduque.dev |
| Panel admin | https://agc.donduque.dev/admin |
| Imagen GHCR | `ghcr.io/yusney/agc-assessors:latest` |
