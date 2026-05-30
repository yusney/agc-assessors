# Proposal: Offices Feature

## Intent

AGC Assessors needs a fully operational offices directory. Currently the `offices-management` spec in `agc-mvp` defines the data model and seeds, but no domain layer, public-facing page, home section, or navigation entry exists. This change delivers the complete vertical slice: admin CRUD → domain entity → public listing page → Google Maps home section → navbar link.

## Scope

### In Scope
- `Office` domain entity + repository interface (Clean Architecture, zero framework deps)
- Eloquent model with `spatie/laravel-translatable` (name, address, city in ca/es/en)
- `offices` DB migration + index on `is_active` + seeder for 6 offices
- Filament 5 `OfficeResource` (list, create, edit, delete; translatable tabs)
- `HomeSectionType::OFFICES_MAP` — configurable title/subtitle/cta via `HomeSectionResource`
- Home section Blade partial: full-width Google Maps embed + card grid below
- Public page `/oficines` · `/es/oficinas` · `/en/offices` with map + full office cards
- Localized routes via `LaravelLocalization::getLocalizedURL`
- Navbar menu item seeded pointing to offices page
- Lang keys in `resources/lang/{ca,es,en}/messages.php`

### Out of Scope
- Individual office detail pages (single-office view)
- Directions / transit API (only "Como llegar" deep-link to Google Maps)
- CMS-editable map styling / custom map themes
- Server-side sitemap entry (deferred to SEO phase)

## Capabilities

### New Capabilities
- `offices-domain`: Office entity, OfficeRepository interface, Eloquent model + mapper, migration, seeder
- `offices-admin`: Filament 5 OfficeResource — list (name, city, active toggle), create/edit/delete with translatable tabs
- `offices-public-page`: Public `/oficines|oficinas|offices` controller + Blade view — map embed + card grid
- `offices-home-section`: `offices_map` home section type partial + HomeSectionResource integration

### Modified Capabilities
- `navigation`: Add seeded menu item pointing to offices page (delta to `agc-mvp/specs/navigation/spec.md`)
- `homepage-dynamic-zones`: Register `offices_map` as a valid `type` (delta to `agc-mvp/specs/homepage-dynamic-zones/spec.md`)

## Approach

Follow the domain/repository pattern already established by News/Service (recommended in explore.md):

1. **Domain** — `AGC\Domain\Offices\Entities\Office` (immutable VO fields), `OfficeRepository` interface
2. **Infrastructure** — `EloquentOffice` model with `HasTranslations`, `EloquentOfficeRepository` mapper, bound in `OfficesServiceProvider`
3. **Admin** — `AGC\Filament\Resources\OfficeResource` using `Filament\Schemas\Schema`, strict_types, final class
4. **Public** — `App\Http\Controllers\Public\OfficesController` → calls repository → passes JSON markers + offices collection to Blade
5. **Home section** — `resources/views/public/home-sections/offices_map.blade.php` dispatched from existing `home.blade.php` type switch
6. **Maps** — pure JS: API key from `config('services.google_maps.key')`, markers built from controller-injected JSON array

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `src/Domain/Offices/` | New | Entity, RepositoryInterface |
| `src/Infrastructure/Persistence/Eloquent/Models/EloquentOffice.php` | New | Translatable Eloquent model |
| `src/Infrastructure/Persistence/Eloquent/Repositories/EloquentOfficeRepository.php` | New | Mapper + query logic |
| `app/Providers/OfficesServiceProvider.php` | New | Bind interface → implementation |
| `database/migrations/*_create_offices_table.php` | New | Table + `is_active` index |
| `database/seeders/OfficeSeeder.php` | New | 6 office records (ca/es/en) |
| `src/Filament/Resources/OfficeResource.php` | New | Admin CRUD resource |
| `app/Http/Controllers/Public/OfficesController.php` | New | Public page controller |
| `resources/views/public/pages/offices/index.blade.php` | New | Listing page — map + cards |
| `resources/views/public/home-sections/offices_map.blade.php` | New | Home section partial |
| `routes/web.php` | Modified | Add localized offices routes |
| `resources/lang/{ca,es,en}/messages.php` | Modified | Nav label + page copy |
| `database/seeders/MenuItemSeeder.php` | Modified | Add offices nav item |
| `config/services.php` | Modified | `google_maps.key` entry |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Google Maps API key exposed in frontend | Med | Pass via Blade variable only; never commit to `.env.example` with real value |
| Translatable JSON fallback inconsistency | Low | Follow existing `$model->field()->get(app()->getLocale())` pattern; add unit test |
| DB-driven navbar: menu item order collision | Low | Seed with explicit `order` value after existing items |
| Filament 5 namespace confusion on actions | Med | Follow AGENTS.md table; use `Filament\Actions\*` exclusively |

## Rollback Plan

1. `php artisan migrate:rollback` removes `offices` table
2. Delete seeded menu item via Filament or direct DB query
3. Remove `OfficesServiceProvider` from `config/app.php` providers array
4. Drop `offices_map` partial — `@includeIf` will silently skip missing partials
5. Revert `routes/web.php` and lang files via git

## Dependencies

- `spatie/laravel-translatable` — already installed (used by News/Service)
- `mcamara/laravel-localization` — already installed
- Google Maps JS API key provisioned in `.env` as `GOOGLE_MAPS_KEY`

## Success Criteria

- [ ] All 6 seeded offices appear in Filament admin list
- [ ] Create / edit / delete an office through admin; changes reflected on public page
- [ ] `/oficines`, `/es/oficinas`, `/en/offices` return HTTP 200 with map + cards
- [ ] Google Maps renders markers for all active offices; info window shows name + address
- [ ] Offices nav item appears in navbar and routes correctly per locale
- [ ] `offices_map` home section renders when configured in HomeSectionResource
- [ ] PHPStan level 8 passes; `php artisan test` green
