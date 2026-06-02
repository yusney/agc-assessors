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

## Paso 5 — Configurar volúmenes persistentes (CRÍTICO)

En Dokploy → tu app → **Volumes / Mounts** → **Add Volume**:

Usar **Volume Mount** (no Bind Mount) para cada uno:

| Volume Name | Mount Path (en el contenedor) | ¿Por qué? |
|-------------|-------------------------------|-----------|
| `agc-storage` | `/var/www/html/storage` | Persiste imágenes, logs, caché de Laravel entre redeploys |
| `agc-cache` | `/var/www/html/bootstrap/cache` | Persiste caché de bootstrap (rutas, config, views compiladas) |

> **CRÍTICO**: Sin `agc-storage`, todas las imágenes subidas desde el panel de admin se pierden en cada redeploy. El contenedor se destruye y se crea uno nuevo desde la imagen — solo los volúmenes sobreviven.

> **IMPORTANTE sobre el symlink**: La imagen Docker ahora incluye un startup script que **crea automáticamente** el enlace `public/storage → storage/app/public` en cada arranque del contenedor. No hace falta configurar entrypoint manual en Dokploy.

### ¿Por qué no podemos crear el symlink en el Dockerfile?

Si creamos `public/storage` durante el `docker build`, al arrancar el contenedor el volumen `agc-storage` se monta **sobre** `/var/www/html/storage` y el symlink queda roto (apunta a un directorio que fue reemplazado por el volumen). Por eso el symlink se debe crear **en runtime**, no en build time.

---

## Paso 6 — Entrypoint (no es necesario configurar)

> **Nota**: La imagen `ghcr.io/yusney/agc-assessors:latest` ya incluye un startup script que se ejecuta automáticamente al arrancar el contenedor. No hace falta configurar nada en Dokploy → Advanced → Command / Entrypoint.
>
> Si dejaste un entrypoint manual de una versión anterior, **borralo** para que no interfiera con el script automático.

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

# Logs del contenedor (verificar que el startup script corrió)
docker logs $(docker ps -q -f name=agc) --tail 50

# Smoke test HTTP
curl -s -o /dev/null -w "%{http_code}" https://agc.donduque.dev/

# Verificar que el symlink público fue creado por el startup script
docker exec -it $(docker ps -q -f name=agc) ls -la /var/www/html/public/storage
# → lrwxrwxrwx ... storage -> /var/www/html/storage/app/public

# Verificar que las imágenes subidas son accesibles
docker exec -it $(docker ps -q -f name=agc) ls /var/www/html/public/storage/ | head -5

# Probar acceso web a una imagen (reemplazar con un UUID real)
curl -I https://agc.donduque.dev/storage/XXXX-XXXX-XXXX-XXXX.jpg
# → HTTP/2 200
```

---

## Troubleshooting

### 403 Forbidden en el panel admin
El modelo `User` debe implementar `FilamentUser` con `canAccessPanel()` retornando `true`. Ver `app/Models/User.php`.

### Assets cargando en HTTP (mixed content)
Verificar que `TRUSTED_PROXIES=*` está configurado en las variables de entorno. Sin esto, Laravel no detecta que está detrás de Traefik y genera URLs HTTP.

### Imágenes de la biblioteca no se ven en el frontend

Síntoma: las imágenes subidas desde el panel de admin (Filament/Curator) se ven en el backend pero dan 404 en el frontend.

**Diagnóstico paso a paso:**

1. **Verificar que el volumen `agc-storage` está montado** en `/var/www/html/storage`:
   ```bash
   docker exec -it $(docker ps -q -f name=agc) ls -la /var/www/html/storage/app/public/
   ```
   Debería listar los archivos subidos. Si está vacío, el volumen no se montó correctamente.

2. **Verificar que el symlink `public/storage` existe**:
   ```bash
   docker exec -it $(docker ps -q -f name=agc) ls -la /var/www/html/public/storage
   ```
   Debería mostrar:
   ```
   lrwxrwxrwx ... storage -> /var/www/html/storage/app/public
   ```

3. **Verificar que el startup script corrió** (en los logs del contenedor):
   ```bash
   docker logs $(docker ps -q -f name=agc) | grep -i "symlink\|storage"
   ```
   Debería ver:
   ```
   🔗 Created symlink: /var/www/html/public/storage -> /var/www/html/storage/app/public
   ```

4. **Si el symlink no existe**, ejecutar manualmente:
   ```bash
   docker exec -it $(docker ps -q -f name=agc) sh -c "ln -s /var/www/html/storage/app/public /var/www/html/public/storage"
   ```

5. **Verificar permisos**:
   ```bash
   docker exec -it $(docker ps -q -f name=agc) sh -c "chown -R www-data:www-data /var/www/html/storage"
   ```

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
