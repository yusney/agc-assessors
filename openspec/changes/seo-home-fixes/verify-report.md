# Verification Report: seo-home-fixes

**Change**: `seo-home-fixes` — SEO Fixes — Public Frontend  
**Version**: N/A (delta spec)  
**Mode**: Standard (Strict TDD active in config, but no covering tests exist for this change)  
**Artifact Path**: `openspec/changes/seo-home-fixes/verify-report.md`  
**Date**: 2026-06-01  

---

## Completeness

| Metric | Value |
|--------|-------|
| Tasks total | 11 |
| Tasks complete | 2 |
| Tasks partial | 2 |
| Tasks incomplete | 7 |

**Task breakdown** (from `tasks.md`):

| # | Task | Status | Notes |
|---|------|--------|-------|
| 1.1 | Create `SeoComposer` | **INCOMPLETE** | Missing canonical resolution, breadcrumbs, `Organization`/`LocalBusiness` schemas |
| 1.2 | Create schema partial | **PARTIAL** | Guard works, but missing `JSON_HEX_TAG` flag per spec |
| 1.3 | Add `og-default.jpg` | **INCOMPLETE** | File does not exist |
| 2.1 | Modify layout (og/Twitter/canonical) | **PARTIAL** | og/Twitter present; canonical still uses `url()->current()`, not `$canonicalUrl` from composer |
| 2.2 | Include schema partial in `<head>` | **COMPLETE** | Correctly included before `@vite` |
| 2.3 | Register `SeoComposer` in `AppServiceProvider` | **PARTIAL** | Registered in `boot()`, but only for `layouts.public` instead of `*` |
| 3.1 | Add `stats_heading` translation + sr-only `<h2>` | **PARTIAL** | sr-only `<h2>` present; key uses `stats.title` instead of `stats_heading` |
| 3.2 | Defer Leaflet in `offices_map.blade.php` | **INCOMPLETE** | No `IntersectionObserver`, no `defer` attr, CSS still pushed to `head` stack |
| 4.1 | PHPStan level 8 on `SeoComposer` | **INCOMPLETE** | PHPStan binary not available in container |
| 4.2 | Run full test suite | **INCOMPLETE** | 6 pre-existing failures; zero new tests for SEO layer |
| 4.3 | Manual SEO validation | **PARTIAL** | Some tags present, core schemas missing |

---

## Build & Tests Execution

**Build (PHP syntax check)**: ⚠️ Partial  
```text
$ docker compose exec php php -l app/Http/ViewComposers/SeoComposer.php
No syntax errors detected in app/Http/ViewComposers/SeoComposer.php

$ docker compose exec php php -l app/Providers/AppServiceProvider.php
No syntax errors detected in app/Providers/AppServiceProvider.php
```

**Tests**: ❌ 10 passed, 6 failed  
```text
$ docker compose exec php php artisan test
...
Tests:    6 failed, 10 passed (35 assertions)
Duration: 0.77s

Failures are pre-existing (OfficeEntityTest ArgumentCountError) and unrelated to this change.
No new tests were written for SeoComposer, schema partial, or SEO meta tags.
```

**Static Analysis (PHPStan)**: ➖ Not available  
```text
$ docker compose exec php vendor/bin/phpstan analyse app/Http/ViewComposers/SeoComposer.php --level=8
OCI runtime exec failed: vendor/bin/phpstan: no such file or directory
```

**Coverage**: ➖ Not available (no SEO-specific tests).

---

## Spec Compliance Matrix

### Capability: `seo-structured-data`

| Requirement | Scenario | Test | Result |
|-------------|----------|------|--------|
| JSON-LD Partial Rendered in `<head>` | Home page — sitewide schemas present | (none) | ❌ **FAILING** — `Organization` and `LocalBusiness` schemas missing; only `AccountingService` + `WebSite` present |
| JSON-LD Partial Rendered in `<head>` | JSON encodes non-ASCII safely | (none) | ⚠️ **PARTIAL** — `JSON_UNESCAPED_UNICODE` present; `JSON_HEX_TAG` missing |
| BreadcrumbList Per Route | Home page breadcrumb | (none) | ❌ **FAILING** — `getBreadcrumbSchema()` exists but never called in `compose()` |
| BreadcrumbList Per Route | Inner page breadcrumb | (none) | ❌ **FAILING** — No breadcrumbs injected |
| Schema Partial Receives `$schemas` | Empty schemas array — no output | (none) | ✅ **COMPLIANT** — `@if(!empty($schemas))` guard present |
| Schema Partial Receives `$schemas` | Multiple schemas — multiple script blocks | (none) | ⚠️ **PARTIAL** — Loop exists, but only 2 schemas emitted (expected 3+) |
| SeoComposer Builds Without Side-effects | SiteSetting has no `og_image` | (none) | ✅ **COMPLIANT** — Graceful null-safe fallback |

### Capability: `seo-social-meta`

| Requirement | Scenario | Test | Result |
|-------------|----------|------|--------|
| Open Graph Image Meta Tag | SiteSetting has custom `og_image` | (none) | ✅ **COMPLIANT** — Logic present; runtime unverified without DB seed |
| Open Graph Image Meta Tag | SiteSetting null / missing | (none) | ❌ **FAILING** — Falls back to empty string because `og-default.jpg` missing (404) |
| Open Graph Type Tag | `og:type` always present | Runtime grep | ✅ **COMPLIANT** — `<meta property="og:type" content="website">` present |
| Twitter Card Meta Tags | Twitter card renders with default image | Runtime grep | ❌ **FAILING** — `twitter:image` absent when `ogImage` is empty |
| Twitter Card Meta Tags | Twitter card renders with admin-configured image | (none) | ✅ **COMPLIANT** — Logic matches `og:image` exactly |
| OG Default Image Asset | Asset accessible via browser | `curl /images/og-default.jpg` | ❌ **FAILING** — HTTP 404; file not committed |

### Capability: `seo-canonical`

| Requirement | Scenario | Test | Result |
|-------------|----------|------|--------|
| Canonical Tag Present | Home page — Catalan locale | Runtime grep | ⚠️ **PARTIAL** — Tag present, but from `url()->current()`, not `SeoComposer` |
| Canonical Tag Present | Home page — Spanish locale | Runtime grep | ⚠️ **PARTIAL** — Same; `$canonicalUrl` from composer never used |
| Canonical Tag Present | Inner page — English locale | (none) | ⚠️ **PARTIAL** — Unverified; design indicates same fallback path |
| Canonical Resolved via LaravelLocalization | Locale middleware sets locale before composer | (none) | ❌ **FAILING** — `SeoComposer` does NOT resolve canonical; layout uses `url()->current()` |
| Canonical Resolved via LaravelLocalization | Canonical never contains `index.php` or query strings | Runtime curl `/?foo=bar` | ✅ **COMPLIANT** — `url()->current()` strips query strings |
| SeoComposer Registered in `boot()` | Correct registration order | Static check | ⚠️ **PARTIAL** — Registered in `boot()`, but only for `layouts.public`, not `*` |

### Capability: `seo-accessibility`

| Requirement | Scenario | Test | Result |
|-------------|----------|------|--------|
| Stats Section Has sr-only `<h2>` | Heading present in rendered HTML | Runtime grep | ✅ **COMPLIANT** — `<h2 id="stats-heading" class="sr-only">AGC Assessors en xifres</h2>` found |
| Stats Section Has sr-only `<h2>` | Heading text is translated per locale | Runtime grep | ✅ **COMPLIANT** — Catalan text rendered correctly |
| Translation Key `stats_heading` | Key defined in all three locales | `php artisan tinker` (manual) | ⚠️ **PARTIAL** — Key is `stats.title`, not `stats_heading` as specified; values exist in all 3 locales |
| Heading Level Continuity | axe-core heading order audit passes | (none) | ✅ **COMPLIANT** — `<h2>` placed inside `<section>` after hero `<h1>` |

### Capability: `seo-performance`

| Requirement | Scenario | Test | Result |
|-------------|----------|------|--------|
| Leaflet Assets Absent from `<head>` | Home page — no map section | `curl / | grep leaflet` | ⚠️ **PARTIAL** — Homepage includes map partial, so Leaflet IS present (expected). Contact page correctly has NONE |
| Leaflet Assets Absent from `<head>` | Offices page — Leaflet loaded asynchronously | `curl /oficines | grep leaflet` | ⚠️ **PARTIAL** — JS at end of `<body>`, but CSS still inside `<head>` via `@stack('head')`; no `defer` attr on script |
| Map Initialises After Leaflet JS | Map renders correctly after defer | (none) | ⚠️ **PARTIAL** — `DOMContentLoaded` wrapper present; no `IntersectionObserver` or init guard flag |
| Map Initialises After Leaflet JS | Map container hidden/offscreen | (none) | ❌ **FAILING** — No `IntersectionObserver`; map initialises immediately on DOM ready regardless of visibility |
| Preconnect Hints Added | Google Fonts preconnect present | Runtime grep | ✅ **COMPLIANT** — `fonts.googleapis`, `fonts.gstatic.com` + `crossorigin` present |
| Preconnect Hints Added | Preconnect before stylesheets | Runtime source order | ✅ **COMPLIANT** — `preconnect` / `dns-prefetch` links appear before Google Fonts CSS links |

**Compliance summary**: 9/26 scenarios compliant, 5 partial, 12 failing/untested

---

## Correctness (Static Evidence)

| Requirement | Status | Notes |
|------------|--------|-------|
| `SeoComposer` schema building | ❌ Incorrect | Returns `AccountingService` instead of `Organization`; `LocalBusiness` entirely absent |
| `SeoComposer` breadcrumb builder | ❌ Incorrect | `getBreadcrumbSchema()` implemented but never invoked in `compose()` |
| `SeoComposer` OG image resolver | ⚠️ Partial | Null-safe fallback chain exists, but terminal asset (`og-default.jpg`) missing |
| `SeoComposer` canonical resolver | ❌ Incorrect | Method missing; `$canonicalUrl` never composed |
| Schema partial rendering | ✅ Implemented | `@if(!empty($schemas))` guard + `@foreach` loop correct |
| Schema partial JSON flags | ⚠️ Deviation | Uses `JSON_PRETTY_PRINT \| JSON_UNESCAPED_UNICODE \| JSON_UNESCAPED_SLASHES`; spec requires `JSON_UNESCAPED_UNICODE \| JSON_HEX_TAG` |
| OG meta tags in layout | ✅ Implemented | `og:title`, `og:description`, `og:type`, `og:url`, conditional `og:image` present |
| Twitter Card meta tags | ✅ Implemented | `twitter:card`, `twitter:title`, `twitter:description`, conditional `twitter:image` present |
| Canonical tag in layout | ⚠️ Deviation | Still uses `@hasSection('seo_canonical')` / `url()->current()` fallback; not replaced with `$canonicalUrl` from composer |
| Locale-aware canonical | ❌ Missing | `LaravelLocalization::getLocalizedURL()` never used for canonical |
| Query-string stripping | ✅ Implemented | `url()->current()` strips query strings (verified runtime) |
| sr-only heading in stats | ✅ Implemented | `<h2 id="stats-heading" class="sr-only">` present with `aria-labelledby` on `<section>` |
| Translation keys in all locales | ✅ Implemented | `stats.title` exists in `ca`, `es`, `en` with correct translations |
| Leaflet deferred loading | ⚠️ Partial | JS pushed to bottom of body; CSS still in `<head>`; no `defer` attribute |
| `IntersectionObserver` guard | ❌ Missing | No observer, no guard flag — map initialises unconditionally on `DOMContentLoaded` |
| Preconnect / dns-prefetch hints | ✅ Implemented | Google Fonts, unpkg, OSM hints present and in correct order |
| `og-default.jpg` committed | ❌ Missing | File does not exist in repository or container |

---

## Design Coherence

| Decision | Followed? | Notes |
|----------|-----------|-------|
| ViewComposer approach for canonical + schemas | ⚠️ Partial | `SeoComposer` created but only resolves `$schemas` and `$ogImage`; canonical and breadcrumbs omitted |
| Blade partial for JSON-LD (`public/components/schema`) | ✅ Yes | Partial created, included in layout, receives `$schemas` array |
| Plain PHP arrays for schemas (no side-effects) | ✅ Yes | No HTTP calls, DB writes, or queue jobs during composition |
| `JSON_UNESCAPED_UNICODE` encoding | ✅ Yes | Present in both composer and partial |
| Null-safe `SiteSetting` handling | ✅ Yes | `getOgImage()` and `getLogoUrl()` guard against `null` and empty string |
| Register composer in `boot()`, not `register()` | ✅ Yes | `AppServiceProvider::boot()` used |
| Register composer for all public views (`*`) | ❌ No | Only registered for `layouts.public`; functionally equivalent today but deviates from design |
| `@push('scripts')` / `@push('styles')` for Leaflet | ⚠️ Partial | Uses `@push('head')` for CSS instead of `@push('styles')` |
| `IntersectionObserver` + guard flag for map | ❌ No | Not implemented |
| Default OG image asset committed | ❌ No | Asset missing |

---

## Issues Found

### CRITICAL

1. **Missing `Organization` and `LocalBusiness` schemas** — Spec requires both `@type` nodes in JSON-LD. `SeoComposer` only emits `AccountingService` and `WebSite`. No `Organization` or `LocalBusiness` present.
2. **Missing `BreadcrumbList` injection** — `getBreadcrumbSchema()` exists but is never called in `compose()`. Spec requires a `BreadcrumbList` on every public page.
3. **Canonical URL not resolved by `SeoComposer`** — `tasks.md` explicitly says to replace the `@hasSection('seo_canonical')` block with `<link rel="canonical" href="{{ $canonicalUrl }}">` from composer. Layout still uses `url()->current()` fallback. `LaravelLocalization::getLocalizedURL()` is never invoked.
4. **Missing `public/images/og-default.jpg`** — Spec requires a committed 1200×630 asset. File absent; `curl /images/og-default.jpg` returns 404. OG image falls back to empty string.
5. **No `IntersectionObserver` guard on map init** — Spec requires `IntersectionObserver` on `#offices-map-home` plus a guard flag to ensure `L.map()` runs exactly once. Current code initialises unconditionally on `DOMContentLoaded`.
6. **Strict TDD violation — zero covering tests** — Config enforces strict TDD. No tests exist for `SeoComposer`, schema partial, meta tag rendering, or breadcrumb logic. Existing suite has 6 pre-existing failures.
7. **PHPStan not runnable** — Config requires PHPStan level 8. Binary unavailable in container, so static analysis gate cannot pass.

### WARNING

8. **`SeoComposer` registered only for `layouts.public`** — Spec says `View::composer('*', ...)`. Current registration is `View::composer('layouts.public', ...)`. Functional today, but deviates.
9. **Stats translation key mismatch** — Spec and tasks specify `messages.stats_heading`. Implementation uses `messages.stats.title`. Values exist in all 3 locales, so functionally works, but key name deviates from spec.
10. **Leaflet CSS still inside `<head>`** — Pushed to `@stack('head')` (inside `<head>`) instead of `@push('styles')` at end of `<body>`. Still render-blocking on map pages.
11. **Leaflet `<script>` missing `defer` attribute** — Spec requires `defer`. Script is at bottom of `<body>` but lacks explicit `defer`.
12. **Schema partial JSON flags deviation** — Uses `JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES` instead of `JSON_UNESCAPED_UNICODE | JSON_HEX_TAG`.
13. **`getWebsiteSchema()` search URL hardcodes locale prefix for default locale** — Produces `https://agcassessors.com/ca?q=...` even though `ca` has no URL prefix in routing.

### SUGGESTION

14. Add `JSON_HEX_TAG` to schema partial for safety (prevents `</script>` injection if schema data ever includes user input).
15. Consider seeding `SiteSetting` keys (`contact`, `organization_addresses`, `og_image`, `logo_url`) so schema generation has real data in local/dev environments.
16. Add `og:image:alt` meta tag for accessibility completeness.

---

## Severity Counts

| Severity | Count |
|----------|-------|
| CRITICAL | 7 |
| WARNING | 6 |
| SUGGESTION | 3 |

---

## Verdict

**FAIL**

The implementation of `seo-home-fixes` is incomplete and does not satisfy multiple spec requirements. Core deliverables are missing: `Organization`/`LocalBusiness` schemas, `BreadcrumbList`, `LaravelLocalization`-based canonical resolution, `og-default.jpg` asset, and the `IntersectionObserver` map guard. Additionally, strict TDD mode is active but no covering tests were written, and PHPStan is unavailable. The change requires additional apply work before it can pass verification.
