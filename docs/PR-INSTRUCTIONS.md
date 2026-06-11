# Pull Request — feat/local-seo-offices

> **Status**: rama pusheada al remote. PR pendiente de creación manual (no automática).

## ⚠ Por qué no se pudo crear automáticamente

El token de GitHub configurado en el entorno (`GH_TOKEN=ghp_****…****`) es un PAT clásico **sin los scopes `public_repo` ni `repo`** necesarios para crear PRs via API. `gh pr create` falla con:

```
pull request create failed: GraphQL: Your token has not been granted the required scopes to execute this query.
The 'createPullRequest' field requires one of the following scopes: ['public_repo']
```

La solución más limpia es crear el PR desde el navegador (1 click) usando la URL de abajo. Si querés habilitar la creación automática para futuras PRs, regenera el PAT en https://github.com/settings/tokens con scope `public_repo` (o `repo` si es privado) y configuralo con `gh auth login --with-token`.

## 🚀 Crear el PR (recomendado, 1 click)

**URL para crear el PR:**

https://github.com/yusney/agc-assessors/pull/new/feat/local-seo-offices

GitHub te muestra un formulario pre-rellenado con la rama de origen (`feat/local-seo-offices`) y destino (`master`). Hacé clic en **Create pull request**.

**Título sugerido** (pegar tal cual):

```
feat(seo): local multi-office SEO + i18n fixes (13 commits)
```

**Descripción sugerida** (pegar tal cual en el cuerpo del PR):

````markdown
## Summary

Implementación completa de SEO local multi-sede para AGC Assessors más
cuatro fixes de i18n encontrados durante la verificación. Cada una de las
6 oficinas (Caldes de Montbui, Sant Celoni, Mollet del Vallès, Granollers,
Prats de Lluçanès, Manlleu) tiene ahora su propia URL con schema
`LocalBusiness` válido, NAP semántico, horarios estructurados, área de
servicio y hreflang en 3 idiomas. El switcher de idioma de la navbar
mantiene la página actual al cambiar de idioma y el `<html lang>` se
corresponde con la URL en los 3 locales.

## What changed

### Feature principal: SEO local multi-oficina
- 3 migraciones nuevas (opening_hours + service_area + image_alt, slug, manager_*)
- Nueva entidad de dominio `Office` con 6 getters adicionales + `publicSlug()`
- Nuevo repositorio `OfficeRepositoryInterface::findActiveBySlug()`
- `OfficeResource` (Filament admin) con secciones SEO: horarios, pueblos, slug, responsable
- Nuevo `OfficesController::show($slug)` para páginas individuales
- Nuevo schema `LocalBusiness` × 1 por página individual con: NAP, geo, telephone, email, priceRange, openingHoursSpecification parseado, areaServed, parentOrganization
- Nuevo schema `ItemList` × 1 por hub listando las 6 oficinas
- Hreflang alternates × 4 (ca, es, en, x-default) en todas las páginas
- Hero split 2-columnas (título + imagen) en cada oficina con placeholder
- Mapa Leaflet con marker por oficina
- Card "Conoce AGC Assessors" con CTAs a contenido común
- Breadcrumb accesible (aria-label traducido)
- Seeder `OfficeSeoSeeder` que carga datos plausibles desde JSON
- i18n: 11+ claves nuevas en ca/es/en

### Fixes de i18n (3 issues resueltos)
- **Switcher preserves the current page**: `/es/oficinas/granollers` → click "EN" → `/en/oficinas/granollers` (antes 404)
- **Switcher highlights the active locale**: `<html lang>` y la opción highlighted coinciden con la URL en los 3 locales
- **Zero `?locale=` query strings**: en todas las URLs internas (hubs, cards, individuales, breadcrumb, navbar). Antes había 9 ocurrencias por página
- **Tabla resumen removida** del hub de oficinas (redundante con las cards)

### a11y
- 12 elementos con `focus-visible:ring-2` añadido
- `<img>` con `width`, `height`, `loading`, `decoding`, `fetchpriority`
- `text-wrap: balance` en H1
- aria-labels traducidos (breadcrumb, mobile menu, search)

## Audit results

Resultado del audit script `docs/SEO-AUDIT-SCRIPT.py` (simula Google
Rich Results Test) sobre 12 páginas:

| Check | Resultado |
|---|---|
| HTTP 200 | ✓ 12/12 |
| Title length (≤ 60) | ✓ 12/12 |
| Description length (100-160) | ✗ 1/12 (Mollet — placeholder de prueba) |
| Canonical URL | ✓ 12/12 |
| Hreflang alternates (4) | ✓ 12/12 |
| H1 único | ✓ 12/12 |
| H2+ presentes | ✓ 12/12 |
| `<html lang>` matching URL | ✓ 12/12 |
| Switcher active = URL locale | ✓ 12/12 |
| `?locale=` en links internos | ✓ 12/12 (0 ocurrencias) |
| `LocalBusiness` schema válido | ✓ 5/6 (Manlleu sin phone) |
| `ItemList` schema válido | ✓ 3/3 |
| `openingHoursSpecification` HH:MM | ✓ 6/6 |

## Known issues (no bloqueantes, post-merge)

1. **Manlleu sin teléfono** (en BD desde el seed inicial). El schema `LocalBusiness` no emite `telephone` para esa oficina. **Solución**: añadir teléfono real vía `/admin/offices`.
2. **6 oficinas sin `image`** (campo `cover_media_id` vacío). La página muestra un placeholder con la inicial. **Solución**: subir foto real en Filament.
3. **Datos plausibles pero inventados** (responsables, horarios, pueblos). El seeder cargó estos datos como placeholder. **Solución**: revisar y reemplazar con datos reales en `/admin/offices`. Hay un valor de descripción "prueba de descricio" en Mollet que también es dato de prueba.
4. **Token de `gh` sin scopes** — no afecta al código, solo a la automatización de PRs.

## Action items post-merge (no-code, marketing)

- Crear 6 fichas de Google Business Profile (uno por oficina)
- Pedir reseñas de clientes reales en cada GBP — factor #1 de ranking local
- Backlinks desde directorios locales (Páginas Amarillas, Cámaras de Comercio)
- Regenerar el token de GitHub con scope `public_repo` para automatizar futuros PRs

## Test plan

```bash
# Re-run audit any time:
python3 docs/SEO-AUDIT-SCRIPT.py

# Verify pages render with correct lang and zero ?locale=:
for path in "" "es" "en" "es/oficines" "es/oficines/granollers" "oficines" "oficines/granollers"; do
  curl -sL "http://localhost:8080/$path" | \
    grep -oE '<html lang="[^"]+"' && \
    curl -sL "http://localhost:8080/$path" | grep -c 'locale=' || true
done

# Manual switcher test:
# 1. Open /es/oficinas/granollers
# 2. Click "CA" in the navbar
# 3. URL should become /oficines/granollers (no ?locale=)
# 4. CA option should be highlighted in the switcher
# 5. <html lang> should be "ca"
```

## Files changed (24 archivos)

### Migraciones (3 nuevos)
- `database/migrations/2026_06_10_153327_add_local_seo_fields_to_offices.php`
- `database/migrations/2026_06_10_160339_add_slug_to_offices.php`
- `database/migrations/2026_06_10_160355_add_manager_fields_to_offices.php`

### Capa de dominio
- `src/Domain/Offices/Entities/Office.php` (entity)
- `src/Domain/Offices/Repositories/OfficeRepositoryInterface.php`

### Capa de infraestructura
- `src/Infrastructure/Persistence/Eloquent/Models/EloquentOffice.php`
- `src/Infrastructure/Persistence/Eloquent/Repositories/EloquentOfficeRepository.php`

### Capa de aplicación
- `app/Http/Controllers/Public/OfficesController.php` (+show method)
- `app/Http/Middleware/SetLocaleFromUrl.php` (nuevo, 39 lines)
- `app/Http/View/Composers/SeoComposer.php` (LocalBusiness, ItemList, helpers)
- `app/Http/View/Composers/SeoComposer.php` ($localizedUrl global helper)

### Capa de presentación
- `resources/views/public/components/navbar.blade.php` (i18n aria-labels, $localizedUrl)
- `resources/views/public/home-sections/offices_map.blade.php` (links limpios)
- `resources/views/public/pages/offices/index.blade.php` (cards sin tabla, $officePath)
- `resources/views/public/pages/offices/show.blade.php` (página individual)

### Capa de rutas y traducciones
- `routes/web.php` (3 grupos por locale, orden correcto, middleware setLocaleFromUrl)
- `resources/lang/{ca,es,en}/messages.php` (i18n keys)

### Seeder y datos
- `database/seeders/OfficeSeoSeeder.php` (nuevo)
- `storage/backups/offices_seo_content.json` (datos plausibles)

### Documentación
- `docs/SEO-GUIDE.md` (nuevo, manual de SEO para el cliente)
- `docs/SEO-OFFICES-PLAN.md` (nuevo, plan técnico original)
- `docs/SEO-AUDIT-SCRIPT.py` (nuevo, script de auditoría reutilizable)
- `docs/SEO-AUDIT-REPORT.txt` (nuevo, output de la auditoría)
- `docs/PR-INSTRUCTIONS.md` (este archivo)

## Commit list (13 commits)

```
6f6a764 fix(i18n): zero ?locale= query strings on every page (final sweep)
a403e99 fix(i18n): stop appending ?locale= to internal office links
4e02f01 refactor(offices): remove redundant summary table from offices index
3377599 fix(i18n): switcher now highlights locale matching the URL
5011dc7 fix(i18n): preserve current page in locale switcher and register routes per locale
1f861d3 docs: add PR creation instructions and full PR description
135d0f5 fix(seo): normalize opening hours to HH:MM in LocalBusiness schema
0d4d781 feat(offices): populate 6 offices with plausible SEO data and trim titles
007a422 fix(a11y): web interface guidelines compliance for office pages
9b33a01 feat(offices): add hero cover image to individual office pages
e571c6a feat(home): link office cards and map popups to individual office pages
ea9aecb feat(seo): individual office pages with LocalBusiness per location
fc3df51 feat(seo): local multi-office schema + summary table for /oficinas
```
````

## Comando para regenerar el token y automatizar

Si querés habilitar la creación automática de PRs para futuras ramas:

1. Ir a https://github.com/settings/tokens
2. **Generate new token** → tipo **Fine-grained** (recomendado) o **Classic**
3. Para Fine-grained: en "Repository access" seleccionar `yusney/agc-assessors`, en "Permissions" añadir `Contents: Read and write` y `Pull requests: Read and write`
4. Para Classic: seleccionar scope `public_repo` (o `repo` si el repo es privado)
5. Guardar el token y exportarlo: `export GH_TOKEN=ghp_NUEVOTOKEN`
6. Reintentar: `gh pr create --base master --head feat/local-seo-offices --title "..." --body "..."`

## Estado del push

```
$ git log --oneline master..feat/local-seo-offices
6f6a764 fix(i18n): zero ?locale= query strings on every page (final sweep)
a403e99 fix(i18n): stop appending ?locale= to internal office links
4e02f01 refactor(offices): remove redundant summary table from offices index
3377599 fix(i18n): switcher now highlights locale matching the URL
5011dc7 fix(i18n): preserve current page in locale switcher and register routes per locale
1f861d3 docs: add PR creation instructions and full PR description
135d0f5 fix(seo): normalize opening hours to HH:MM in LocalBusiness schema
0d4d781 feat(offices): populate 6 offices with plausible SEO data and trim titles
007a422 fix(a11y): web interface guidelines compliance for office pages
9b33a01 feat(offices): add hero cover image to individual office pages
e571c6a feat(home): link office cards and map popups to individual office pages
ea9aecb feat(seo): individual office pages with LocalBusiness per location
fc3df51 feat(seo): local multi-office schema + summary table for /oficinas

$ git diff --stat master..feat/local-seo-offices | tail -1
 24 files changed, 2166 insertions(+), 94 deletions(-)
```
