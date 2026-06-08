# Proposal: SEO Multilingual Metadata

## Intent

News, Pages, and Services have partial per-language SEO fields in the DB but the implementation is broken in multiple places: `services.show` ignores SEOData entirely, `keywords` fields are DB-orphaned, hreflang alternate links are completely absent (critical for multilingual Google indexing), no global SEO defaults admin UI exists, and no sitemap is generated. This change closes every multilingual SEO gap across the public site in a structured, reviewable way.

## Scope

### In Scope
- Fix `services.show` to use `$service->seo()->title()` / `$service->seo()->description()` with fallback
- Add `<link rel="alternate" hreflang>` for all 3 locales in base layout
- Add `og:locale` / `og:locale:alternate` OpenGraph tags in layout
- Add optional `seo_keywords` JSON column to `news_articles`, `pages`, `services`; render `<meta name="keywords">` in layout when non-empty
- Add `SeoSettingsPage` (Filament) for global SEO defaults (title, description, og:image) per locale via `SiteSetting`
- Add `spatie/laravel-sitemap` and multilingual sitemap route (`/sitemap.xml`)
- Update `robots.txt` to reference sitemap URL

### Out of Scope
- Per-record SEO for Offices, Team Members, Home Sections — **by design**: none have detail pages; all inherit from global defaults and translation keys
- Paid SEO analytics or third-party crawl integrations
- Content rewriting or copy quality improvements
- Schema.org expansion beyond existing `SeoComposer` JSON-LD (optional follow-up)
- `noindex`/`nofollow` per-page toggle (deferred nice-to-have)
- Per-content `og:image` override (requires separate media feature)

### Meta Keywords Stance
Google has ignored `<meta name="keywords">` since 2009. The field is included **only as optional admin data** for potential use by other search engines or internal tooling. It is NOT a ranking signal. If the user prefers not to add it, it can be dropped from PR2 with zero SEO impact.

## Capabilities

### New Capabilities
- `seo-multilingual-head`: hreflang alternates + og:locale + og:locale:alternate in public layout, locale-aware fallback strategy
- `seo-global-defaults`: Filament settings page for site-wide meta title/description/og:image per locale using `SiteSetting`
- `seo-sitemap`: multilingual XML sitemap via `spatie/laravel-sitemap` (all active URLs × 3 locales)
- `seo-keywords`: optional `seo_keywords` JSON column + backend forms + `<meta name="keywords">` rendering

### Modified Capabilities
- `seo-infrastructure` (agc-mvp): extend keyword persistence to News/Pages/Services; add keywords to SEOData mapping in repositories
- `seo-social-meta` (seo-home-fixes): add `og:locale` and `og:locale:alternate` tags alongside existing og:title/og:description

## Approach

Three chained PRs, each independently reviewable and deployable:

1. **PR1 – Foundation & Head Rendering** (zero migrations): Fix `services.show` view to use SEOData; add hreflang alternates loop using `LaravelLocalization::getSupportedLocales()` + `getLocalizedURL()`; add `og:locale` / `og:locale:alternate`; add keywords meta rendering (guarded by `@if($seoKeywords ?? '')`); add SiteSetting-backed global defaults to `SeoComposer`; update `robots.txt`.

2. **PR2 – Admin Fields & Persistence**: Migration adding `seo_keywords` JSON column to `news_articles`, `pages`, `services`; update `$translatable` arrays in Eloquent models; update repositories to map keywords into SEOData; add keywords fields to `NewsResource`, `PageResource`, `ServiceResource`; implement `SeoSettingsPage` in Filament.

3. **PR3 – Sitemap & Verification**: Add `spatie/laravel-sitemap`; configure multilingual sitemap generator (SitemapController or artisan command); register `GET /sitemap.xml` route; Pest feature test asserting sitemap XML contains all active content URLs; HTML assertion tests for hreflang and og:locale.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `resources/views/layouts/public.blade.php` | Modified | hreflang alternates, og:locale, keywords meta |
| `resources/views/public/services/show.blade.php` | Modified | Use `$service->seo()->title()` instead of `$service->name()` |
| `app/Http/ViewComposers/SeoComposer.php` | Modified | Add global defaults from SiteSetting, keywords resolution |
| `src/Filament/Pages/SeoSettingsPage.php` | New | Global SEO defaults per locale (title, description, og:image) |
| `src/Filament/Resources/{News,Page,Service}Resource.php` | Modified | Add seo_keywords repeater/fields per locale |
| `src/Infrastructure/Persistence/Eloquent/Models/{News,Page,Service}Model.php` | Modified | Add `seo_keywords` to `$translatable` |
| `src/Infrastructure/Persistence/Eloquent/Repositories/Eloquent{News,Page,Service}Repository.php` | Modified | Map keywords from Eloquent → SEOData |
| `database/migrations/*_add_seo_keywords_to_content_tables.php` | New | `seo_keywords` JSON nullable column |
| `routes/web.php` | Modified | Add `/sitemap.xml` route |
| `app/Http/Controllers/Public/SitemapController.php` | New | Multilingual sitemap generation |
| `public/robots.txt` | Modified | Add `Sitemap:` reference |
| `composer.json` | Modified | Add `spatie/laravel-sitemap` |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| hreflang URL mismatch (wrong locale in href) | Med | Use `LaravelLocalization::getLocalizedURL($locale, null, [], true)` + assert in test |
| `spatie/laravel-sitemap` version conflict | Low | Check compat before PR3; has Laravel 11+ support |
| SeoSettingsPage key collision with existing SiteSetting | Low | Use namespaced keys `seo.global.{locale}.title` |
| keywords meta tag rendered as empty string | Low | Guard with `@if(!empty($seoKeywords))` |
| `services.show` fallback chain not covering all edge cases | Med | Explicit `$service->seo()->title() ?: $service->name()` pattern, tested in feature test |

## Rollback Plan

- **PR1**: `git revert` — Blade-only + SeoComposer changes, zero migrations required
- **PR2**: `git revert` + `php artisan migrate:rollback` (additive nullable JSON columns; no data loss)
- **PR3**: `git revert` + `composer remove spatie/laravel-sitemap`

## Dependencies

- `mcamara/laravel-localization` — already installed
- `spatie/laravel-translatable` — already installed
- `SiteSetting` model — already installed and working
- `SeoComposer` ViewComposer — already registered (from `seo-home-fixes` change)

## Delivery Plan (Chained PRs)

| PR | Scope | Est. Lines | Risk |
|----|-------|-----------|------|
| PR1 | services.show fix + hreflang + og:locale + keywords meta + SeoComposer global defaults + robots.txt | ~120 | Low |
| PR2 | seo_keywords migration + models + repos + Filament resources + SeoSettingsPage | ~280 | Med |
| PR3 | spatie/laravel-sitemap + SitemapController + route + feature tests + HTML assertions | ~160 | Low |
| **Total** | 3 PRs | **~560** | Med |

> **Chained PR required** — total estimate (~560 lines) exceeds the 400-line review budget. Each PR is independently testable and deployable without breaking the next.

## QA Plan

- **PR1 — HTML assertions**: Pest `get('/')->assertSee('<link rel="alternate" hreflang="ca"', false)` for all 3 locales; assert `og:locale` present; assert `services.show` renders seo_title from DB
- **PR2 — Admin round-trip**: Feature test: set keywords via Filament → assert rendered in `<meta name="keywords">`; assert `SeoSettingsPage` saves and reflects on frontend
- **PR3 — Sitemap smoke**: `get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml')` + assert contains known news article URL in 3 locale variants; manual browser check in each locale

## Success Criteria

- [ ] `services.show` renders seo_title/seo_description from SEOData with content fallback
- [ ] All public pages include `<link rel="alternate" hreflang>` for ca/es/en + x-default
- [ ] `og:locale` + `og:locale:alternate` present in `<head>` on every page
- [ ] Keywords `<meta>` rendered when non-empty; absent when empty
- [ ] Global SEO defaults editable in Filament and reflected in frontend `<head>`
- [ ] `/sitemap.xml` returns valid XML with all active News/Pages/Services URLs × 3 locales
- [ ] `robots.txt` references `/sitemap.xml`
- [ ] PHPStan level 8 passes on all new/modified files
- [ ] All existing tests green after each PR
