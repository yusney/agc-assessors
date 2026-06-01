# seo-canonical Specification

## Purpose

Emit a `<link rel="canonical">` tag on every public page with the correct absolute URL for
the current locale, preventing duplicate content issues across `ca` / `es` / `en` variants
and ensuring search engines index the intended URL for each route.

---

## Requirements

### Requirement: Canonical Tag Present on Every Public Page

The system MUST render exactly one `<link rel="canonical" href="...">` in `<head>` on every
public page response.

#### Scenario: Home page — Catalan locale (default, no prefix)

- GIVEN the base locale is `ca` with no URL prefix
- WHEN a visitor requests `https://agcassessors.com/`
- THEN `<link rel="canonical" href="https://agcassessors.com/">` MUST appear in `<head>`

#### Scenario: Home page — Spanish locale

- GIVEN the `es` locale uses the `/es` prefix
- WHEN a visitor requests `https://agcassessors.com/es`
- THEN `<link rel="canonical" href="https://agcassessors.com/es">` MUST appear in `<head>`

#### Scenario: Inner page — English locale

- GIVEN the `en` locale uses the `/en` prefix
- WHEN a visitor requests `https://agcassessors.com/en/services`
- THEN `<link rel="canonical" href="https://agcassessors.com/en/services">` MUST appear

---

### Requirement: Canonical URL Resolved via LaravelLocalization

`SeoComposer` MUST resolve the canonical URL using
`LaravelLocalization::getLocalizedURL(app()->getLocale(), request()->getPathInfo())` (or
equivalent) so the canonical always reflects the active locale prefix correctly.

#### Scenario: Locale middleware sets locale before ViewComposer runs

- GIVEN the locale middleware runs before the controller/view is rendered
- WHEN `SeoComposer::compose()` is called
- THEN `app()->getLocale()` MUST already equal the request's locale
- AND the resolved canonical URL MUST include the correct locale prefix (or none for `ca`)

#### Scenario: Canonical never contains `index.php` or query strings

- GIVEN any public GET request (with or without query params)
- WHEN the canonical tag is rendered
- THEN the `href` MUST NOT include query strings (e.g., `?utm_source=...`)
- AND the `href` MUST NOT include `index.php`

---

### Requirement: SeoComposer Registered in AppServiceProvider `boot()`

`SeoComposer` MUST be registered via `View::composer('*', SeoComposer::class)` inside
`AppServiceProvider::boot()`, NOT in `register()`, so locale middleware has already
executed before composition.

#### Scenario: Correct registration order

- GIVEN a request that sets locale via middleware
- WHEN `boot()` registers the composer and the view is rendered
- THEN `app()->getLocale()` is the correct locale at composer execution time

---

## Validation

| Check | Method |
|---|---|
| Canonical present | `curl -s https://agcassessors.com/ \| grep 'canonical'` |
| Locale-correct canonical on `/es` | `curl -s https://agcassessors.com/es \| grep 'canonical'` |
| No query string in canonical | Request `/?foo=bar`; assert canonical has no `?` |
| PHPStan level 8 | `phpstan analyse app/Http/ViewComposers/SeoComposer.php` |

---

## Dependencies

- `mcamara/laravel-localization` — `getLocalizedURL()` helper
- `App\Http\ViewComposers\SeoComposer` — shared composer (also used by `seo-structured-data`)
- `AppServiceProvider::boot()` — registration point
- Locale middleware must run before view composition (guaranteed by Laravel middleware pipeline)
