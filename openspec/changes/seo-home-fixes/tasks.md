# Tasks: SEO Fixes — Public Frontend

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~200 (7 files + 3 translation keys + binary asset) |
| 400-line budget risk | Low |
| Chained PRs recommended | No |
| Suggested split | Single PR |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Low

### Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Full SEO layer: composer + schema + layout + stats + map | PR 1 | main; well under 400 lines |

---

## Phase 1: Foundation — SeoComposer + Schema Partial

- [ ] 1.1 Create `app/Http/ViewComposers/SeoComposer.php` — `final class SeoComposer` implementing `Illuminate\Contracts\View\Composer`. Resolve `$canonicalUrl` via `LaravelLocalization::getLocalizedURL(app()->getLocale(), request()->getPathInfo())` (strip query strings). Build `$schemas` array: Organization (`LegalService` subtype), LocalBusiness, WebSite (`SearchAction` targeting `?s={search_term_string}`), BreadcrumbList (1 item for home). Resolve `$ogImage` from `SiteSetting::get('og_image')` with fallback to `asset('images/og-default.jpg')`. Handle null SiteSetting gracefully — no exceptions. Use `JSON_UNESCAPED_UNICODE | JSON_HEX_TAG`. `declare(strict_types=1)`.
- [ ] 1.2 Create `resources/views/public/components/schema.blade.php` — Blade partial receiving `$schemas` array. Loop and emit `<script type="application/ld+json">` blocks via `@json($schema, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)`. Guard: render nothing if `$schemas` is empty.
- [ ] 1.3 Add `public/images/og-default.jpg` — 1200×630 px JPG, under 200 KB, representing AGC Assessors brand identity. Use the existing logo/color palette (`#00346f`).

## Phase 2: Wire into Base Layout

- [ ] 2.1 Modify `resources/views/layouts/public.blade.php` — add `<meta property="og:image" content="{{ $ogImage }}">`, `<meta property="og:image:width" content="1200">`, `<meta name="twitter:card" content="summary_large_image">`, `<meta name="twitter:image" content="{{ $ogImage }}">`. Replace `@hasSection('seo_canonical')` block with `<link rel="canonical" href="{{ $canonicalUrl }}">` from composer.
- [ ] 2.2 Include schema partial in layout — add `@include('public.components.schema', ['schemas' => $schemas ?? []])` inside `<head>`, before `@vite`.
- [ ] 2.3 Register SeoComposer in `app/Providers/AppServiceProvider.php` — add `use Illuminate\Support\Facades\View;` and `View::composer('*', \App\Http\ViewComposers\SeoComposer::class);` inside `boot()`. Add required `use` import.

## Phase 3: Page-Specific Fixes

- [ ] 3.1 Add `stats_heading` to `resources/lang/{ca,es,en}/messages.php` — ca: "Les nostres xifres", es: "Nuestras cifras", en: "Our numbers". Then modify `resources/views/public/home-sections/stats.blade.php`: add `<h2 class="sr-only">{{ __('messages.stats_heading') }}</h2>` as the first child of the `<section>` (after the `@if` block opens).
- [ ] 3.2 Defer Leaflet in `resources/views/public/home-sections/offices_map.blade.php` — move `<link rel="stylesheet">` and `<script src="...leaflet.js">` out of inline render. Wrap JS init in an `IntersectionObserver` guard on `#offices-map-home`. Keep `DOMContentLoaded` fallback. Ensure `L.map()` is called exactly once (guard flag).

## Phase 4: Verification

- [ ] 4.1 Run PHPStan level 8 on `SeoComposer` — `docker compose exec php vendor/bin/phpstan analyse app/Http/ViewComposers/SeoComposer.php` — must pass with zero errors.
- [ ] 4.2 Run full test suite — `docker compose exec php php artisan test` — all existing tests must remain green.
- [ ] 4.3 Manual SEO validation — verify canonical URL on `/`, `/es`, `/en/services`. Check JSON-LD presence in `<head>` (Organization, LocalBusiness, WebSite, BreadcrumbList). Verify `og:image` and `twitter:card` meta tags render. Confirm `<h2 class="sr-only">` in stats section. Confirm Leaflet CSS/JS absent from `<head>` on non-map pages. Lighthouse SEO score ≥ 95.
