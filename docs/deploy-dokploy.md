# Despliegue en Dokploy — AGC Assessors

Guía para desplegar AGC Assessors en producción usando Dokploy con una imagen precompilada e inmutable de GitHub Container Registry (GHCR).

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
- PHP 8.4 + Nginx (ServerSideUp v4.5.0 al fijar la imagen, tag legible `serversideup/php:8.4-fpm-nginx`, fijado por digest en el Dockerfile)
- Assets compilados con Node 24 Alpine y pnpm 10.32.1; Node, npm, pnpm y `node_modules` no existen en la imagen final
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

Elegir **una** modalidad de despliegue. La configuración de entorno no es intercambiable entre ambas.

### Opción A — Docker Image directo en Dokploy

1. Dokploy → **Create Application**
2. **Type**: Docker Image
3. **Image**: `ghcr.io/yusney/agc-assessors@sha256:...` (el digest publicado para el tag de trazabilidad `sha-<COMMIT_SHA_COMPLETO>`)
4. **Registry**: GitHub Container Registry
   - Username: `yusney`
   - Token: Personal Access Token con `read:packages`

En esta modalidad, las variables de runtime se configuran en **Dokploy → Environment**. `AGC_ENV_FILE` no se usa. La aplicación hereda directamente el `HEALTHCHECK` definido en `docker/php/Dockerfile.production`, que ejecuta el checker HTTP, de PostgreSQL y de migraciones incluido en la imagen.

### Opción B — Docker Compose

Si Dokploy despliega este repositorio con `docker-compose.production.yml`, configurar las variables de interpolación de Compose:

```env
AGC_IMAGE_DIGEST=sha256:<DIGEST_PUBLICADO>
AGC_ENV_FILE=/etc/agc/production.env
```

`AGC_IMAGE_DIGEST` y `AGC_ENV_FILE` son variables de interpolación de Compose. Compose falla de forma explícita si falta cualquiera. El digest debe ser el publicado por CI, no un tag ni la referencia completa de la imagen. `AGC_ENV_FILE` debe ser una ruta absoluta a un archivo externo al repositorio, legible solo por el operador de despliegue y el daemon de Docker.

---

## Paso 3 — Variables de entorno de runtime

Configurar el siguiente entorno de runtime según la modalidad elegida:

- **Docker Image directo**: introducir las variables en **Dokploy → Environment**. No definir `AGC_ENV_FILE`.
- **Docker Compose**: crear fuera del repositorio el archivo indicado por `AGC_ENV_FILE` y guardar allí estas variables.

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

> Laravel confía en el proxy de Dokploy desde `bootstrap/app.php`. Mantén `APP_URL` en HTTPS y verifica que Traefik envíe correctamente `X-Forwarded-Proto`.
>
> Los valores entre `<...>` son marcadores. Sustituirlos por secretos gestionados en Dokploy; no guardar valores reales en el repositorio ni en esta guía.
>
> No uses el archivo `.env` del checkout. En Docker Image directo, Dokploy inyecta el entorno configurado en su UI. En Compose, `AGC_ENV_FILE` apunta a un archivo externo que no se versiona ni se copia dentro de la imagen.

---

## Paso 4 — Configurar el puerto

En Dokploy → tu app → **General**:

- **Port**: `8080`

---

## Paso 5 — Configurar volúmenes persistentes (CRÍTICO)

En Dokploy → tu app → **Volumes / Mounts** → **Add Volume**:

Usar **Volume Mount** (no Bind Mount):

| Volume Name | Mount Path (en el contenedor) | ¿Por qué? |
|-------------|-------------------------------|-----------|
| `agc-storage` | `/var/www/html/storage` | Persiste imágenes, logs y estado de runtime entre redeploys |

> **CRÍTICO**: Sin `agc-storage`, todas las imágenes subidas desde el panel de admin se pierden en cada redeploy. El contenedor se destruye y se crea uno nuevo desde la imagen — solo los volúmenes sobreviven.
>
> **No persistas `bootstrap/cache`**. Compose lo monta como `tmpfs` escribible por `www-data`; desaparece al recrear el contenedor para que ningún manifiesto de una versión anterior sobreviva al deploy.

> **IMPORTANTE sobre el symlink**: La imagen incluye el enlace inmutable `public/storage → /var/www/html/storage/app/public`. En cada arranque, el startup script crea el directorio de destino dentro del volumen y verifica el enlace; nunca escribe en `public`.

### Por qué el enlace se crea en el Dockerfile

El enlace usa un destino absoluto. Al montar `agc-storage` sobre `/var/www/html/storage`, Docker reemplaza el contenido del destino, no el enlace situado en `public`. Esto permite mantener todo el código y `vendor` como `root` y sin permisos de escritura, dejando `storage` y el `tmpfs` efímero de `bootstrap/cache` escribibles por `www-data`.

---

## Paso 6 — Entrypoint (no es necesario configurar)

> **Nota**: La imagen fijada por digest ya incluye un startup script que se ejecuta automáticamente al arrancar el contenedor. No hace falta configurar nada en Dokploy → Advanced → Command / Entrypoint.
>
> Si dejaste un entrypoint manual de una versión anterior, **borralo** para que no interfiera con el script automático.
>
> El startup automático verifica los mounts, prepara los subdirectorios de `storage`, elimina manifiestos PHP generados que pudieran estar obsoletos y regenera package discovery en el `tmpfs` de `bootstrap/cache`. Falla claramente si el enlace fue sustituido o los paths no son escribibles por `www-data`. No ejecuta migraciones, seeders, Shield, Tinker ni comandos de autenticación, y no modifica la base de datos.
>
> Producción hereda la configuración segura de Nginx incluida en ServerSideUp v4. Los archivos `docker/nginx/nginx.conf` y `docker/nginx/sites/default.conf` no se instalan: fueron escritos para otra topología (puerto 80, usuario `nginx`, PHP-FPM en `php:9000` y proxy Vite) y no son compatibles con el proceso integrado de ServerSideUp en el puerto 8080.
>
> Compose aplica `no-new-privileges`, elimina todas las capabilities y monta `/tmp` como `tmpfs` con `noexec`, `nosuid` y `nodev`. No activa `read_only` para toda la raíz porque ServerSideUp genera su configuración de Nginx bajo `/etc/nginx` al arrancar; la inmutabilidad de la aplicación se aplica con propiedad `root:root` y permisos sin escritura sobre código y `vendor`.

---

## Paso 7 — Crear la base de datos

En Dokploy → **Databases** → **Create Database**:

- **Type**: PostgreSQL
- **Name**: `agc`

Dokploy genera las credenciales automáticamente. Copiarlas a las variables de entorno del Paso 3.

---

## Paso 8 — Primer arranque antes de exponer tráfico público

1. Click en **Deploy** en Dokploy
2. Esperar que Dokploy pull la imagen de GHCR y arranque el contenedor
3. Verificar en **Logs** que el contenedor arrancó sin errores

Mantener deshabilitados el dominio y el proxy, y comprobar que el puerto publicado del host o su firewall no permita acceso público. El healthcheck permanecerá en estado `unhealthy` hasta que exista la tabla `migrations` y no haya migraciones pendientes; esto es esperado durante el bootstrap inicial. No exponer el servicio públicamente hasta completar el bootstrap y obtener estado `healthy`.

---

## Paso 9 — Inicializar la base de datos y el acceso admin

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

## Paso 10 — Verificar salud y habilitar el dominio

1. Esperar a que el contenedor esté `healthy` después de completar las migraciones y el bootstrap:

```bash
docker inspect --format '{{.State.Health.Status}}' $(docker ps -q -f name=agc)
```

2. Solo cuando el resultado sea `healthy`, configurar en Dokploy → tu app → **Domains**:

- **Domain**: `agc.donduque.dev`
- **HTTPS**: activar (Traefik gestiona el certificado Let's Encrypt automáticamente)
- **Port**: `8080`

3. Habilitar tráfico y ejecutar el smoke test HTTPS de la sección **Verificación post-deploy**.

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

### Migraciones en cada deploy

Antes de enviar tráfico al nuevo digest o ejecutar el redeploy:

1. Crear y verificar un backup recuperable de PostgreSQL.
2. Revisar las migraciones del digest objetivo. Deben seguir expand-contract: primero cambios aditivos compatibles con la imagen actual y la nueva; los cambios destructivos se aplican en un deploy posterior, cuando la imagen anterior ya no recibe tráfico y terminó la ventana de rollback.
3. Ejecutar las migraciones con la imagen objetivo y el mismo archivo de entorno externo:

```bash
AGC_IMAGE_DIGEST=sha256:<DIGEST_PUBLICADO> \
AGC_ENV_FILE=/etc/agc/production.env \
docker compose -f docker-compose.production.yml run --rm --no-deps php artisan migrate --force
```

4. Solo después de una migración correcta, hacer el redeploy del mismo digest y habilitar tráfico. Si una migración no es compatible hacia atrás, detener el despliegue; no confiar en un rollback de imagen para revertir el esquema.

Este procedimiento recurrente no sustituye el bootstrap P0 explícito del Paso 10: Shield, roles y creación de administrador se ejecutan únicamente durante esa inicialización.

Cada push a `main` o `master` protegido:

1. GitHub Actions ejecuta los quality gates, escanea la imagen y la publica a GHCR (~1-2 min)
2. En Dokploy actualizar la referencia al digest de imagen y hacer **Redeploy**. El tag SHA completo solo sirve para localizar y trazar la publicación, porque puede republicarse en un rerun.
3. No usar `latest` en producción: no es una referencia inmutable

Con Compose, copiar únicamente la parte `sha256:...` a `AGC_IMAGE_DIGEST` y validar antes del redeploy:

```bash
AGC_IMAGE_DIGEST=sha256:<DIGEST_PUBLICADO> \
AGC_ENV_FILE=/etc/agc/production.env \
docker compose -f docker-compose.production.yml config --quiet
```

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

# El healthcheck exige HTTP /up, PostgreSQL select 1, la tabla migrations y cero migraciones pendientes
docker inspect --format '{{.State.Health.Status}}' $(docker ps -q -f name=agc)

# Verificar el symlink público inmutable
docker exec -it $(docker ps -q -f name=agc) ls -la /var/www/html/public/storage
# → lrwxrwxrwx ... storage -> /var/www/html/storage/app/public

# Verificar identidad y límites de escritura
docker exec -it $(docker ps -q -f name=agc) sh -c 'id && test ! -w /var/www/html/app && test ! -w /var/www/html/vendor && test -w /var/www/html/storage && test -w /var/www/html/bootstrap/cache'

# Verificar que las imágenes subidas son accesibles
docker exec -it $(docker ps -q -f name=agc) ls /var/www/html/public/storage/ | head -5

# Probar acceso web a una imagen (reemplazar con un UUID real)
curl -I https://agc.donduque.dev/storage/XXXX-XXXX-XXXX-XXXX.jpg
# → HTTP/2 200
```

---

## Troubleshooting

### 403 Forbidden en el panel admin
Verifica que el usuario tenga uno de los roles autorizados (`super_admin`, `manager`, `editor` o `viewer`). No desactives el control de `canAccessPanel()`. Si falta el administrador, sigue el bootstrap explícito del Paso 10.

### Assets cargando en HTTP (mixed content)
Verifica que `APP_URL` use `https://` y que Traefik envíe `X-Forwarded-Proto: https`. La aplicación ya confía en el proxy desde `bootstrap/app.php`.

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
    Runtime storage and Laravel package manifest are ready.
   ```

4. **Recuperar permisos del volumen con un contenedor auxiliar de una sola ejecución**. El servicio permanece como `www-data`, sin capabilities; el helper obtiene únicamente `CHOWN` durante este comando:
   ```bash
   AGC_IMAGE_DIGEST=sha256:<DIGEST_DESPLEGADO> \
   AGC_ENV_FILE=/etc/agc/production.env \
   docker compose -f docker-compose.production.yml run --rm --no-deps \
     --user 0:0 --cap-add CHOWN --entrypoint chown php \
     -R 33:33 /var/www/html/storage
   ```

Si `public/storage` no es el enlace incluido en la imagen, no lo recrees dentro del contenedor: redeployá el digest correcto. El startup falla a propósito antes de servir tráfico para evitar mutar el código de producción.

### Error `LocaleViewPath` al arrancar
El alias en `bootstrap/app.php` debe ser `LaravelLocalizationViewPath`, no `LocaleViewPath`.

### Permisos de storage

No intentes ejecutar `chown` dentro del contenedor de aplicación: corre como `www-data`, tiene todas las capabilities eliminadas y debe permanecer así. Usa el helper efímero con privilegio limitado:

```bash
AGC_IMAGE_DIGEST=sha256:<DIGEST_DESPLEGADO> \
AGC_ENV_FILE=/etc/agc/production.env \
docker compose -f docker-compose.production.yml run --rm --no-deps \
  --user 0:0 --cap-add CHOWN --entrypoint chown php \
  -R 33:33 /var/www/html/storage
```

---

## URLs importantes

| Recurso | URL |
|---------|-----|
| Sitio público | https://agc.donduque.dev |
| Panel admin | https://agc.donduque.dev/admin |
| Imagen GHCR | Tag de trazabilidad: `ghcr.io/yusney/agc-assessors:sha-<COMMIT_SHA_COMPLETO>`; producción: `ghcr.io/yusney/agc-assessors@sha256:...` |
