# Despliegue en Dokploy — AGC Assessors

Guía completa para desplegar AGC Assessors en producción usando Dokploy con imagen Docker pre-compilada desde GitHub Container Registry (GHCR).

---

## Arquitectura

```
GitHub (push a master protegido)
    ↓
GitHub Actions: quality gates
    ↓ build + scan + push
GHCR (tag de trazabilidad `sha-<commit SHA completo>`)
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

El archivo `.github/workflows/docker-build.yml` ejecuta los quality gates (Composer, tests de Laravel, Pint, pnpm y Vite) antes de publicar en GHCR. La publicación solo ocurre en un push a `master` o `main` protegido; mientras no exista protección de rama, queda deshabilitada.

No hace falta crear un secret `GHCR_TOKEN`: el workflow usa el `GITHUB_TOKEN` automático, con `contents: read`, `packages: write` e `id-token: write` únicamente en el job de publicación.

La imagen resultante puede consumirse con el tag de trazabilidad `ghcr.io/yusney/agc-assessors:sha-<COMMIT_SHA_COMPLETO>`, que identifica el commit pero puede volver a publicarse si se reejecuta el workflow. Para garantizar la inmutabilidad en producción, se debe usar el digest de imagen `ghcr.io/yusney/agc-assessors@sha256:...`. `latest` es solo un alias informativo del branch por defecto protegido y no debe usarse en producción.

Configuración pendiente en GitHub (este cambio no modifica repository settings):

- Proteger `master` (y `main` si se mantiene) y exigir el check `Quality gates` antes de hacer merge.
- Crear el environment `production` con reviewers obligatorios y una regla de branches permitidas.
- Permitir que el `GITHUB_TOKEN` del repositorio publique paquetes y conceder al paquete GHCR acceso al repositorio mediante `packages: write`.

---

## Paso 2 — Crear la aplicación en Dokploy

1. Dokploy → **Create Application**
2. **Type**: Docker Image
3. **Image**: `ghcr.io/yusney/agc-assessors@sha256:...` (el digest publicado para el tag de trazabilidad `sha-<COMMIT_SHA_COMPLETO>`)
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
CURATOR_GLIDE_TOKEN=<CURATOR_GLIDE_TOKEN>

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
>
> Los valores entre `<...>` son marcadores. Sustituirlos por secretos gestionados en Dokploy; no guardar valores reales en el repositorio ni en esta guía.

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

> **Nota**: La imagen fijada por digest ya incluye un startup script que se ejecuta automáticamente al arrancar el contenedor. No hace falta configurar nada en Dokploy → Advanced → Command / Entrypoint.
>
> Si dejaste un entrypoint manual de una versión anterior, **borralo** para que no interfiera con el script automático.
>
> El startup automático solo prepara el enlace `public/storage → storage/app/public`. No ejecuta migraciones, seeders, Shield, Tinker ni comandos de autenticación, y no modifica la base de datos.

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

## Paso 10 — Inicializar la base de datos y el acceso admin

Una vez el contenedor esté corriendo, ejecutar via **Terminal** en Dokploy o SSH los siguientes comandos, en este orden:

1. Ejecutar las migraciones:
```bash
docker exec -it $(docker ps -q -f name=agc) sh -c "cd /var/www/html && php artisan migrate --force"
```

2. Generar los permisos de Filament Shield:

```bash
docker exec -it $(docker ps -q -f name=agc) sh -c "cd /var/www/html && php artisan shield:generate --all --panel=admin --ignore-existing-policies"
```

3. Crear los roles y permisos de la aplicación:

```bash
docker exec -it $(docker ps -q -f name=agc) sh -c "cd /var/www/html && php artisan db:seed --class=RolesAndPermissionsSeeder --force"
```

4. Crear el administrador de forma explícita e interactiva. Sustituir los marcadores por valores reales; la contraseña se solicita de forma oculta y nunca se pasa como argumento:

```bash
docker exec -it $(docker ps -q -f name=agc) sh -c "cd /var/www/html && php artisan app:create-admin '<ADMIN_EMAIL>' --name='<ADMIN_NAME>'"
```

`DatabaseSeeder` es intencionadamente un no-op. No uses `php artisan db:seed` como bootstrap de producción: los seeders de permisos y el administrador deben invocarse explícitamente como se muestra arriba.

---

## Advertencia de rollback de seguridad (P0)

Nunca reviertas a imágenes anteriores a P0 hasta haber rotado o revocado la credencial fija conocida y haber verificado que la cuenta de administrador heredada es segura. Después de esta corrección, promueve únicamente imágenes posteriores a P0.

---

## Paso 11 — Restaurar backup de base de datos (opcional)

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

Cada push a `main` o `master` protegido:

1. GitHub Actions ejecuta los quality gates, escanea la imagen y la publica a GHCR (~1-2 min)
2. En Dokploy actualizar la referencia al digest de imagen y hacer **Redeploy**. El tag SHA completo solo sirve para localizar y trazar la publicación, porque puede republicarse en un rerun.
3. No usar `latest` en producción: no es una referencia inmutable

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
    Storage symlink created: /var/www/html/public/storage -> /var/www/html/storage/app/public
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
| Imagen GHCR | Tag de trazabilidad: `ghcr.io/yusney/agc-assessors:sha-<COMMIT_SHA_COMPLETO>`; producción: `ghcr.io/yusney/agc-assessors@sha256:...` |
