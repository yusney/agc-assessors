# Pull Request

> **Status**: rama `feat/local-seo-offices` pusheada al remote. PR pendiente de creación manual (token de `gh` sin scope `public_repo`).

## URL para crear el PR

https://github.com/yusney/agc-assessors/pull/new/feat/local-seo-offices

> GitHub te mostrará un formulario pre-rellenado con la rama de origen (`feat/local-seo-offices`) y destino (`master`). Hacé clic en **Create pull request**.

## Título sugerido

```
feat(seo): local multi-office SEO + i18n switcher fixes (12 commits)
```

## Descripción sugerida (copiá y pegá)

````markdown
## Summary

Implementación completa de SEO local multi-sede para AGC Assessors más tres
fixes de i18n encontrados durante la verificación. Cada una de las 6
oficinas (Caldes de Montbui, Sant Celoni, Mollet del Vallès, Granollers,
Prats de Lluçanès, Manlleu) tiene ahora su propia URL con schema
`LocalBusiness` válido, NAP semántico, horarios estructurados, área de
servicio y hreflang en 3 idiomas. El switcher de idioma de la navbar
mantiene la página actual al cambiar de idioma y ya no inyecta el
locale como query string en los links internos.

## What changed

### Architecture (feature principal)
- **8 commits** sobre `master`, 24 archivos, +1.801 / -120 líneas
- Clean Architecture respetada: cambios en
  `Domain/Offices/Entities/Office`, `Repositories/OfficeRepositoryInterface`,
  `Infrastructure/Persistence/Eloquent/{Models,Repositories}/EloquentOffice`,
  `Filament/Resources/OfficeResource`, `Http/Controllers/Public/OfficesController`,
  `Http/View/Composers/SeoComposer`, `Http/Middleware/SetLocaleFromUrl`
- 3 migraciones nuevas (`add_local_seo_fields_to_offices`, `add_slug_to_offices`,
  `add_manager_fields_to_offices`)
- 1 seeder nuevo (`OfficeSeoSeeder`) que carga datos plausibles desde
  `storage/backups/offices_seo_content.json`

### New URLs
- `/es/oficinas` — hub con 6 cards (cards ya muestran toda la info
  de cada oficina; la tabla resumen fue removida por redundancia)
- `/es/oficinas/{slug}` — página individual de cada oficina
- Mismas rutas con prefijo `/ca` y `/en` con hreflang automático

### Schema markup
- `LocalBusiness` schema × 1 por página individual con: NAP completo,
  geo, telephone, email, priceRange, `openingHoursSpecification` parseado
  desde texto libre (formato HH:MM), `areaServed` con 5-8 pueblos por
  oficina, `parentOrganization` jerárquico
- `ItemList` schema × 1 por hub listando las 6 oficinas
- Hreflang alternates × 4 (ca, es, en, x-default) en todas las páginas

### UX
- Hero split 2-columnas (título + imagen de portada) en cada oficina
- Placeholder elegante con inicial y hint para subir foto
- Mapa Leaflet con marker por oficina
- Card "Conoce AGC Assessors" con CTAs a contenido común
- Breadcrumb accesible (aria-label traducido a ca/es/en)

### a11y
- 12 elementos con `focus-visible:ring-2` añadido
- `<img>` con `width`, `height`, `loading`, `decoding`, `fetchpriority`
- `text-wrap: balance` en H1
- aria-labels traducidos (breadcrumb, mobile menu, search)

### i18n fixes (3 commits)
- **Switcher preserves the current page**: `/es/oficinas/granollers`
  → click en "EN" → `/en/oficinas/granollers` (antes 404).
- **Switcher highlights the active locale**: `<html lang>` and the
  highlighted option now match the URL across all 3 locales.
- **Zero `?locale=` on internal links**: replaced `route('offices.show',
  ['locale' => …, 'slug' => …])` with a URL builder that uses
  `LaravelLocalization::getDefaultLocale()` (not `config('app.locale')`,
  which the package mutates at runtime). Before: URLs like
  `/en/oficines/granollers?locale=es`. After: clean `/es/oficines/granollers`.

## Audit results

Resultado del audit script `docs/SEO-AUDIT-SCRIPT.py` (simula Google
Rich Results Test) sobre 11 páginas:

| Check | Resultado |
|---|---|
| HTTP 200 | ✓ 11/11 |
| Title length (≤ 60) | ✓ 11/11 |
| Description length (100-160) | ✓ 11/11 |
| Canonical URL | ✓ 11/11 |
| Hreflang alternates (4) | ✓ 11/11 |
| H1 único | ✓ 11/11 |
| H2+ presentes | ✓ 11/11 |
| `LocalBusiness` schema válido | ✓ 5/6 (Manlleu sin phone) |
| `ItemList` schema válido | ✓ 3/3 |
| `openingHoursSpecification` HH:MM | ✓ 6/6 |
| `?locale=` en links internos | ✓ 0 |

## Known issues (no bloqueantes, post-merge)

1. **Manlleu sin teléfono** (en BD desde el seed inicial). El schema
   `LocalBusiness` no emite `telephone` para esa oficina. Solución:
   añadir teléfono real vía `/admin/offices`.
2. **6 oficinas sin `image`** (campo `cover_media_id` vacío). La página
   muestra un placeholder. Solución: subir foto real en Filament.
3. **Datos plausibles pero inventados** (responsables, horarios,
   pueblos). El seeder cargó estos datos como placeholder. Solución:
   revisar y reemplazar con datos reales en `/admin/offices`.

## Action items post-merge (no-code, marketing)

- Crear 6 fichas de Google Business Profile (uno por oficina)
- Pedir reseñas de clientes reales en cada GBP — factor #1 de ranking local
- Backlinks desde directorios locales (Páginas Amarillas, Cámaras de Comercio)

## Test plan

```bash
# Re-run audit any time:
python3 docs/SEO-AUDIT-SCRIPT.py

# Verify pages render:
curl -I http://localhost:8080/es/oficinas
curl -I http://localhost:8080/es/oficinas/granollers
curl -I http://localhost:8080/en/oficines/granollers

# Verify i18n switcher (visual test):
# 1. Open /es/oficinas/granollers
# 2. Click "CA" in the navbar
# 3. URL should become /oficines/granollers (no ?locale=)
# 4. CA option should be highlighted in the switcher
```

## Files changed (24 archivos)

- `database/migrations/2026_06_10_153327_add_local_seo_fields_to_offices.php` (new)
- `database/migrations/2026_06_10_160339_add_slug_to_offices.php` (new)
- `database/migrations/2026_06_10_160355_add_manager_fields_to_offices.php` (new)
- `database/seeders/OfficeSeoSeeder.php` (new)
- `app/Http/Controllers/Public/OfficesController.php` (+29 -0)
- `app/Http/Middleware/SetLocaleFromUrl.php` (new, 39 lines)
- `app/Http/View/Composers/SeoComposer.php` (+231 -0)
- `resources/lang/{ca,es,en}/messages.php` (i18n keys)
- `resources/views/public/components/navbar.blade.php` (i18n aria-labels)
- `resources/views/public/home-sections/offices_map.blade.php` (i18n fixes)
- `resources/views/public/pages/offices/index.blade.php` (table removed,
  URL builder, defaultLocale-aware)
- `resources/views/public/pages/offices/show.blade.php` (individual page)
- `routes/web.php` (per-locale route groups)
- `src/Domain/Offices/Entities/Office.php` (entity)
- `src/Domain/Offices/Repositories/OfficeRepositoryInterface.php`
- `src/Filament/Resources/OfficeResource.php` (admin form)
- `src/Infrastructure/Persistence/Eloquent/Models/EloquentOffice.php`
- `src/Infrastructure/Persistence/Eloquent/Repositories/EloquentOfficeRepository.php`
- `docs/SEO-GUIDE.md` (new)
- `docs/SEO-OFFICES-PLAN.md` (new)
- `docs/SEO-AUDIT-SCRIPT.py` (new)
- `docs/SEO-AUDIT-REPORT.txt` (new)

## Commit list

```
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

## Por qué la creación automática no funcionó

El token de GitHub configurado para `gh` no tiene los scopes
necesarios (`public_repo`). El workaround más limpio es crear el PR
desde el navegador usando la URL de arriba. El push ya está hecho,
así que la URL funciona directamente.
````

## Notas para vos

- Cuando hagas el PR, **revisá la rama base** — el push es a `master` (no a `main`).
- El título sugerido ya menciona los **12 commits** y los **3 fixes de i18n**, no solo el feature de SEO.
- La descripción está pensada para que un revisor entienda todo el alcance sin tener que ir commit por commit.

## Documentation entregada

- `docs/SEO-GUIDE.md` — Manual de SEO para el cliente (qué rellenar, dónde, cómo)
- `docs/SEO-OFFICES-PLAN.md` — Plan técnico original (referencia histórica)
- `docs/SEO-AUDIT-SCRIPT.py` — Script de auditoría reutilizable
- `docs/SEO-AUDIT-REPORT.txt` — Output completo de la auditoría actual
- `docs/PR-INSTRUCTIONS.md` — Este archivo
