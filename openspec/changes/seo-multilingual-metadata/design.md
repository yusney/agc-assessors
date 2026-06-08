# Design: SEO Multilingual Metadata

> **No-keywords stance (locked)**: Meta keywords are out of scope per Engram
> decision `sdd/seo-multilingual-metadata/decision/no-keywords`. No DB column,
> no admin field, no `<meta name="keywords">` tag, no `SEOData::keywords()` reads
> in view layer. The VO's `keywords` field stays as dead code (do not delete
> it — domain entity, used by future SEO providers).

## Technical Approach

Keep domain layer untouched (SEOData + entity wiring already complete for
News/Page/Service). Fix the view layer + view composer first, then add the
admin defaults page, then ship the sitemap. This ordering means each PR is
independently deployable and reversible.

## Current Architecture (relevant parts)

- `src/Domain/Shared/ValueObjects/SEOData.php` — title, description, canonicalUrl, keywords
- Domain entities `NewsArticle`, `Page`, `Service` expose `seo(): SEOData`
- `Eloquent{News,Page,Service}Repository::toDomain()` maps `seo_title`, `seo_description`, `seo_canonical` only
- `app/Http/View/Composers/SeoComposer.php` is registered on `layouts.public` in `AppServiceProvider` and supplies `ogImage`, `canonicalUrl`, JSON-LD `schemas`
- `resources/views/layouts/public.blade.php` yields `seo_title`, `seo_description`, `seo_canonical`, `seo_og_type`; no hreflang, no og:locale, no keywords
- `resources/views/public/services/show.blade.php:3` uses `$service->name()` instead of `$service->seo()->title()` — the central bug
- `SiteSetting` is a key-value model with static `get()`/`set()` and `value` cast to array
- `routes/web.php` wraps public pages in `LaravelLocalization::setLocale()` middleware group; sitemap must live **outside** that group to keep `/sitemap.xml` locale-agnostic
- No `spatie/laravel-sitemap` dependency; no sitemap route/controller
- `public/robots.txt` is bare (`User-agent: * / Disallow:`)

## Architecture Decisions

| # | Decision | Options (tradeoff) | Choice |
|---|----------|--------------------|--------|
| D1 | Where hreflang is generated | (a) In layout Blade (b) In SeoComposer + view partial (c) New ViewModel | (b) — single source, consumed by future AMP/JSON feeds |
| D2 | Sitemap package | (a) `spatie/laravel-sitemap` (b) Hand-rolled XML in `SitemapController` | (b) — sitemap needs 3-locale URL emission + active-only filter, <40 lines of XML; avoids a dependency, keeps PR3 self-contained |
| D3 | Per-record SEO for Offices/Team/HomeSection | (a) Add translatable fields now (b) Skip — they have no detail pages | (b) — already deferred in spec; global defaults + translation keys suffice |
| D4 | SEOData `keywords` field | (a) Repurpose (b) Leave untouched | (b) — leave as dead code, never read it in views, never persist it |
| D5 | SeoSettingsPage key shape | Flat `seo_title_ca` (3 keys) vs namespaced `seo.global.ca.title` | `seo.global.{locale}.{field}` + `seo.global.og_image` — matches spec, future-extensible |
| D6 | Filament 5 form components | Verified: `Section`, `Tabs\Tab`, `TextInput`, `FileUpload`, `Textarea` all in `Filament\Schemas\*` and `Filament\Forms\*` | Use these verbatim; no new components |
| D7 | `<meta name="robots">` change | (a) Keep hardcoded `index, follow` (b) Read from SeoSettingsPage | (a) — out of scope per spec; future "noindex toggle" PR can replace it |

## Data Flow

```
Controller → view('public.services.show', ['service' => $entity])
                         │
                         ▼
              layouts.public (Blade)
   ├── @yield('seo_title')  ← $service->seo()->title()->get($locale) ?: $service->name()
   ├── @yield('seo_description')  ← $service->seo()->description() or excerpt
   ├── SeoComposer (registered)
   │     ├── @include('public.partials.seo', [...])
   │     │     ├── hreflang loop (LaravelLocalization::getSupportedLocales)
   │     │     ├── og:locale + og:locale:alternate
   │     │     ├── twitter:card/title/description
   │     │     └── canonical (from SeoComposer)
   │     └── JSON-LD schemas
   └── $ogImage / global defaults from SiteSetting
```

For sitemap:

```
GET /sitemap.xml  →  SitemapController@index
                       ├── query Eloquent active content (3 models)
                       ├── for each record × 3 locales: append <url>
                       ├── for each static route × 3 locales: append <url>
                       └── return XML (Content-Type: application/xml)
```

## File Changes (3 chained PRs, each ≤ 400 lines)

### PR1 — Foundation & Head Rendering

| File | Action | Description |
|------|--------|-------------|
| `resources/views/public/services/show.blade.php` | Modify | L3: `$service->name()` → `$service->seo()->title()->get($locale) ?: $service->name()`; L4: use `seo()->description()` first, fallback `strip_tags($desc)` |
| `resources/views/public/partials/seo.blade.php` | **New** | hreflang loop, og:locale, og:locale:alternate, twitter:card mirror |
| `resources/views/layouts/public.blade.php` | Modify | Replace inline OG/canonical block with `@include('public.partials.seo', [...])` |
| `app/Http/View/Composers/SeoComposer.php` | Modify | Add `getHreflangAlternates(): array`, `getOgLocaleAlternates(): array`, read global defaults from `SiteSetting::get('seo.global.{locale}.{field}')`; bind to view |
| `public/robots.txt` | Modify | Append `Sitemap: {{ config('app.url') }}/sitemap.xml` |
| `tests/Feature/Http/SeoHeadTest.php` | **New** | Pest-style PHP test: assertSee hreflang × 4, og:locale × 3, services.show title from seo_title |

**Est. lines: ~180** (well under 400). No migrations. No dependency changes.

### PR2 — Admin Global Defaults

| File | Action | Description |
|------|--------|-------------|
| `src/Filament/Pages/SeoSettingsPage.php` | **New** | `Tabs` for ca/es/en, fields: `title`, `description`, single `og_image` (URL input or curator picker) |
| `app/Http/View/Composers/SeoComposer.php` | Modify | Use new `seo.global.{locale}.{title,description}` + `seo.global.og_image` to override og:title/description/og:image when no per-page value is set |
| `src/Filament/Pages/SeoSettingsPage.php` | (new) | Explicit assertion in test: no `keywords` field anywhere |

**Est. lines: ~170**. Note: PR2 from the old proposal is heavily shrunk because
**per-record `seo_keywords` columns are dropped** (no-keywords decision). No
Eloquent/model changes, no migrations, no Filament resource modifications.

### PR3 — Sitemap & Verification

| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/Public/SitemapController.php` | **New** | ~50-line controller: queries active News/Pages/Services + static route list, emits `Url`/`Urlset` XML |
| `routes/web.php` | Modify | Add `Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap')` **outside** the locale group |
| `tests/Feature/Http/SitemapTest.php` | **New** | Assert HTTP 200, `application/xml` header, body contains known slug in 3 locales, unpublished records excluded |
| `tests/Feature/Http/SeoHeadTest.php` | Modify | Add: services.show `<title>` matches `seo_title`; news.show uses entity `seo_description` |

**Est. lines: ~150**.

## Interfaces / Contracts

```php
// SeoComposer new public methods (added, not removed)
public function getHreflangAlternates(): array;       // [['locale' => 'ca', 'url' => '...'], ...]
public function getOgLocaleAlternates(): array;       // ['es_ES', 'ca_ES'] excluding active
public function getGlobalDefaultTitle(?string $locale = null): ?string;
public function getGlobalDefaultDescription(?string $locale = null): ?string;
```

```php
// SitemapController — single action
public function index(): \Symfony\Component\HttpFoundation\Response;
```

`SitemapController` does **not** extend an interface; thin enough to test
directly. Content-type set explicitly: `response()->view(...)` not used;
return `response($xml, 200, ['Content-Type' => 'application/xml'])`.

## Testing Strategy

| Layer | What | Where |
|-------|------|-------|
| Feature | hreflang × 4, og:locale, og:locale:alternate on `/`, `/serveis/{slug}`, `/actualitat/{slug}`, `/pages/{slug}` | `tests/Feature/Http/SeoHeadTest.php` |
| Feature | `services.show` `<title>` from `seo_title`, falls back to `name()` | same |
| Feature | `/sitemap.xml` 200, `application/xml`, slug × 3 locales, excludes unpublished | `tests/Feature/Http/SitemapTest.php` |
| Feature | `/robots.txt` contains `Sitemap:` directive | extend SeoHeadTest |
| Manual | curl each locale, grep hreflang/og:locale; view-source in browser | local + staging |

Existing PHPUnit-feature-test pattern (`OfficesControllerTest`) is the
template. `RefreshDatabase` + `Accept-Language: ca` header. No new failures
expected; PR1/PR2 are pure additive or fix. PR3 is purely additive route.

## Migration / Rollout

- **PR1**: Blade/composer/robots — no migration. `git revert` of the 4 files.
- **PR2**: Filament page only — no migration. `git revert` of the 2 files.
- **PR3**: New route + controller + test — no migration. `git revert` removes route and controller. Sitemap is optional from Google's perspective; loss only delays indexing.

No destructive changes. No DB rollback needed for any PR.

## No-Keywords Rationale (baked into design)

- **D4 + PR2 shrink**: Spec's `seo_keywords` migration, model `$translatable` updates,
  repository `toDomain()` changes, and Filament resource SEO fields for keywords
  are **all dropped**. PR2 becomes "add SeoSettingsPage + global defaults plumbing"
  (~170 lines vs original 280).
- **PR1 layout changes**: Removed `@if(!empty($seoKeywords))` block. Removed
  the `$seoKeywords` variable from SeoComposer.
- **Verification**: `SeoSettingsPage` test explicitly asserts no `keywords`-named
  field exists in the form schema.

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| hreflang URL points to wrong locale (e.g. `/es/ca/foo`) | Med | Use `LaravelLocalization::getLocalizedURL($locale, url()->current(), [], true)` in a `foreach` over `getSupportedLocales()`; assert in SeoHeadTest |
| Sitemap locale URLs miss the active filter (unpublished records leak) | Med | Query by `published = true` / `active = true`; assert count in test |
| Sitemap route caught by localization middleware → URLs become `/ca/sitemap.xml` | Med | Place `Route::get('/sitemap.xml', ...)` **outside** the `Route::group(['prefix' => LaravelLocalization::setLocale()])` block |
| SeoSettingsPage form cache shows old form fields after a partial revert | Low | Single-class file; no migration; revert = file delete |
| `SiteSetting::get` returns `null` for unset key in SeoComposer; og:image empty | Low | Already handled: `getOgImage()` falls back to `public/images/og-default.jpg` then `''` |

## Changed-Lines Forecast

| PR | Additions | Deletions | Net | Cumulative |
|----|-----------|-----------|-----|------------|
| PR1 | ~150 | ~30 | +120 | 120 |
| PR2 | ~165 | ~5 | +160 | 280 |
| PR3 | ~155 | ~5 | +150 | 430 |
| **Total** | **~470** | **~40** | **+430** | — |

All three PRs are ≤ 400 lines each. Total exceeds 400 (chained PR trigger) but
no individual slice does. Delivery strategy: **stacked PRs targeting develop**,
each independently mergeable.

## Open Questions

None blocking. One non-blocker: should `og:locale` codes be `ca_ES`/`es_ES`/`en_GB`
(spec assumes yes) — locked to the values in the spec until product confirms.
