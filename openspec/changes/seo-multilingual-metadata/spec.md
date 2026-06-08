# SEO Multilingual Metadata Specification

## Purpose

Define requirements for multilingual SEO head rendering, content-source selection, global
defaults administration, multilingual URL/canonical behavior, XML sitemap generation, and
automated QA coverage across the AGC public site.

---

## Non-Goals

- **Meta keywords** — `<meta name="keywords">` is out of scope. Google ignores it since 2009;
  no DB column, no admin field, no rendered tag will be added. See Engram decision
  `sdd/seo-multilingual-metadata/decision/no-keywords`.
- Per-record SEO for Offices, Team Members, Home Sections — no detail pages exist; global
  defaults and translation keys are sufficient.
- Content rewriting, analytics integrations, or schema.org expansion beyond the existing
  `SeoComposer` JSON-LD.
- `noindex`/`nofollow` per-page toggle (deferred nice-to-have).
- Per-content `og:image` override (requires a separate media feature).

---

## Requirements

### Requirement: Complete SEO Head Tag Set

The public layout (`layouts/public.blade.php`) MUST render the following tags in `<head>` on
every public page response. Values MUST use the active locale. Tags MUST appear exactly once
each.

| Tag | Required content |
|---|---|
| `<title>` | Resolved SEO title (see Content Source req below) |
| `<meta name="description">` | Resolved SEO description |
| `<link rel="canonical">` | Absolute URL for current page in active locale |
| `<link rel="alternate" hreflang="ca">` | Unprefixed absolute URL |
| `<link rel="alternate" hreflang="es">` | `/es/`-prefixed absolute URL |
| `<link rel="alternate" hreflang="en">` | `/en/`-prefixed absolute URL |
| `<link rel="alternate" hreflang="x-default">` | Same as `ca` alternate URL |
| `<meta property="og:title">` | Resolved SEO title |
| `<meta property="og:description">` | Resolved SEO description |
| `<meta property="og:url">` | Canonical URL |
| `<meta property="og:type">` | `"website"` |
| `<meta property="og:locale">` | Active locale code (e.g. `ca_ES`, `es_ES`, `en_GB`) |
| `<meta property="og:locale:alternate">` | One entry per non-active locale |
| `<meta name="twitter:card">` | `"summary_large_image"` |
| `<meta name="twitter:title">` | Resolved SEO title |
| `<meta name="twitter:description">` | Resolved SEO description |

#### Scenario: All hreflang tags present on any public page

- GIVEN any public page request (home, service detail, news article, static page)
- WHEN the HTML `<head>` is inspected
- THEN `hreflang="ca"`, `hreflang="es"`, `hreflang="en"`, and `hreflang="x-default"` MUST
  all be present
- AND each `href` MUST be the correct absolute localized URL for that locale

#### Scenario: x-default points to the Catalan (unprefixed) URL

- GIVEN the active locale is `es`
- WHEN the layout renders
- THEN `hreflang="x-default"` href MUST equal the `hreflang="ca"` href (no `/ca/` prefix)

#### Scenario: og:locale reflects active locale and alternates cover the rest

- GIVEN the current locale is `en`
- WHEN the head is inspected
- THEN `<meta property="og:locale">` content MUST be `en_GB` (or configured code)
- AND two `og:locale:alternate` tags MUST be present (one for `ca`, one for `es`)

#### Scenario: Twitter card mirrors og values

- GIVEN any public page renders
- WHEN the head is inspected
- THEN `twitter:title` MUST equal `og:title`
- AND `twitter:description` MUST equal `og:description`

---

### Requirement: Content SEO Source Selection

The system MUST resolve SEO title and description using the following priority chain per
content type:

| View | Primary source | First fallback | Final fallback |
|---|---|---|---|
| `news.show` | `$article->seo()->title()` / `seo()->description()` | `$article->title()` / `$article->excerpt()` | `config('app.name')` / `''` |
| `pages.show` | `$page->seo_title[locale]` / `$page->seo_description[locale]` | `$page->title[locale]` | `config('app.name')` / `''` |
| `services.show` | `$service->seo()->title()` / `seo()->description()` | `$service->name()` / `strip_tags($service->description())[0..159]` | `config('app.name')` / `''` |
| Home, offices, team, listing pages | `messages.{section}.seo_title` translation key | Global SiteSetting default | `config('app.name')` / `''` |

The `services.show` view MUST NOT use `$service->name()` as the primary `<title>` source.
It MUST use `$service->seo()->title()` first.

#### Scenario: services.show renders SEO title from SEOData

- GIVEN a Service has `seo_title.ca = "Assessoria Fiscal – AGC"`
- WHEN a user visits the service detail page in locale `ca`
- THEN `<title>Assessoria Fiscal – AGC</title>` MUST appear in the HTML head

#### Scenario: services.show falls back when seo_title is null

- GIVEN a Service has `seo_title.ca = null`
- WHEN a user visits the service detail in locale `ca`
- THEN `<title>` MUST use `$service->name()` as the fallback value
- AND `<title>` MUST NOT be empty or blank

#### Scenario: news.show uses entity SEO description, not raw content

- GIVEN a news article has `seo_description.es = "Descripció optimitzada"`
- WHEN the article renders in locale `es`
- THEN `<meta name="description" content="Descripció optimitzada">` MUST be present

#### Scenario: Listing page uses translation key

- GIVEN no per-record SEO entity is in scope (e.g. offices index)
- WHEN the page renders
- THEN `<title>` MUST use `__('messages.offices.seo_title')` or its equivalent translation

---

### Requirement: Global SEO Defaults Administration

A Filament page `SeoSettingsPage` MUST allow administrators to configure per-locale values:

| Field | SiteSetting key | Purpose |
|---|---|---|
| Default meta title (ca/es/en) | `seo.global.{locale}.title` | `<title>` fallback |
| Default meta description (ca/es/en) | `seo.global.{locale}.description` | `<meta description>` fallback |
| Default OG image URL | `seo.global.og_image` | `og:image` / `twitter:image` |

No keywords field SHALL be present in this form or anywhere in the admin panel.

Values MUST be saved to `SiteSetting` and MUST be reflected in the public `<head>` on the
next page load via `SeoComposer`.

#### Scenario: Global default title used when entity has no SEO title

- GIVEN `seo.global.ca.title = "AGC Assessors – Fiscal i Laboral"` in SiteSetting
- AND a page has `seo_title.ca = null`
- WHEN the page renders in locale `ca`
- THEN `<title>AGC Assessors – Fiscal i Laboral</title>` MUST appear

#### Scenario: No keywords field in the Filament settings form

- GIVEN `SeoSettingsPage` is rendered in Filament admin
- WHEN an admin inspects all form fields
- THEN no field labeled "keywords", "meta keywords", or similar MUST be present

#### Scenario: OG image URL saved globally and reflected in layout

- GIVEN an admin sets default OG image to `https://cdn.agc.com/og.jpg` via `SeoSettingsPage`
- WHEN any public page renders
- THEN `<meta property="og:image" content="https://cdn.agc.com/og.jpg">` MUST be present

---

### Requirement: Multilingual URL and Canonical Behavior

| Locale | URL prefix | x-default |
|---|---|---|
| `ca` | None (site default) | ✅ x-default points to this |
| `es` | `/es/` | — |
| `en` | `/en/` | — |

Canonical URLs MUST be generated via `LaravelLocalization::getLocalizedURL()` with the
active locale and current path. The canonical MUST NOT include query strings.

#### Scenario: Canonical for default locale has no prefix

- GIVEN the active locale is `ca`
- WHEN the canonical tag is generated for `/serveis/comptabilitat`
- THEN `<link rel="canonical" href="https://agcassessors.com/serveis/comptabilitat">` MUST
  render (no locale prefix in the URL)

#### Scenario: Canonical for secondary locale includes prefix

- GIVEN the active locale is `es`
- WHEN the canonical tag is generated
- THEN the canonical `href` MUST begin with `https://agcassessors.com/es/`

#### Scenario: Canonical strips query strings

- GIVEN a request to `/serveis?utm_source=google`
- WHEN canonical is generated
- THEN the canonical `href` MUST NOT contain `?utm_source=google`

---

### Requirement: XML Sitemap

`GET /sitemap.xml` MUST return a valid XML sitemap generated by `spatie/laravel-sitemap`.
The sitemap MUST include:
- All **active** News articles × 3 locales
- All **active** Pages × 3 locales
- All **active** Services × 3 locales
- Static public routes (home, offices index, team index, contact, search) × 3 locales

Draft (inactive/unpublished) content MUST NOT appear in the sitemap.

#### Scenario: Sitemap returns valid XML with correct Content-Type

- GIVEN the application is running and active content exists
- WHEN `GET /sitemap.xml` is requested
- THEN HTTP 200 MUST be returned
- AND the `Content-Type` header MUST be `application/xml` (or `text/xml`)
- AND the response body MUST be well-formed XML

#### Scenario: Sitemap contains all 3 locale variants for a published article

- GIVEN a published news article exists with slug `noticia-test`
- WHEN the sitemap XML is parsed
- THEN URLs for `/noticia-test`, `/es/noticia-test`, and `/en/noticia-test` MUST all appear

#### Scenario: Unpublished content is excluded from sitemap

- GIVEN a news article has `is_published = false`
- WHEN the sitemap is generated
- THEN no URL for that article MUST appear in the sitemap

---

### Requirement: robots.txt Sitemap Directive

`public/robots.txt` MUST include a `Sitemap:` directive pointing to the absolute sitemap URL.

#### Scenario: robots.txt contains Sitemap directive

- GIVEN the application is deployed
- WHEN a crawler fetches `/robots.txt`
- THEN the response body MUST contain `Sitemap: https://agcassessors.com/sitemap.xml`
  (domain resolved from `APP_URL` or equivalent env variable)

---

### Requirement: Automated QA Coverage

Pest feature tests MUST cover the following assertions:

| Test target | Assertion |
|---|---|
| Home (`/`) | All 4 hreflang tags present, `og:locale` + `og:locale:alternate` present |
| `services.show` | SEO title from `seo_title` rendered; hreflang × 4 present |
| `news.show` | SEO description from `seo_description` rendered; hreflang × 4 present |
| `/sitemap.xml` | HTTP 200, `Content-Type: application/xml`, known slugs × 3 locales |
| `/robots.txt` | Response contains `Sitemap:` directive |

PHPStan level 8 MUST pass on all new and modified files.

#### Scenario: Hreflang assertion passes on services.show

- GIVEN a published service with slug `comptabilitat` exists
- WHEN the test issues `GET /serveis/comptabilitat`
- THEN `assertSee('<link rel="alternate" hreflang="ca"', false)` MUST pass
- AND equivalent assertions for `hreflang="es"`, `"en"`, `"x-default"` MUST all pass

#### Scenario: services.show SEO title assertion

- GIVEN a service with `seo_title.ca = "Test SEO Title"` exists
- WHEN the test issues `GET /serveis/{slug}` in locale `ca`
- THEN `assertSee('<title>Test SEO Title</title>', false)` MUST pass
- AND `assertDontSee` of the raw `name()` as `<title>` MUST pass

#### Scenario: Sitemap smoke test covers all 3 locale variants

- GIVEN at least one published news article, page, and service exist
- WHEN the test issues `GET /sitemap.xml`
- THEN HTTP 200 MUST be returned
- AND the response body MUST contain the article's slug in `ca`, `es`, and `en` variants

---

## Validation

```bash
# Hreflang smoke check
curl -s http://localhost:8080/ | grep 'hreflang'

# Sitemap check
curl -s http://localhost:8080/sitemap.xml | head -5

# robots.txt check
curl -s http://localhost:8080/robots.txt | grep 'Sitemap'

# Route list (confirm /sitemap.xml registered)
docker compose exec php php artisan route:list | grep sitemap

# PHPStan (run after implementation)
docker compose exec php ./vendor/bin/phpstan analyse --level=8 \
  app/Http/ViewComposers/SeoComposer.php \
  app/Http/Controllers/Public/SitemapController.php \
  src/Filament/Pages/SeoSettingsPage.php
```

---

## Dependencies

- `mcamara/laravel-localization` — `getLocalizedURL()` and `getSupportedLocales()` helpers
- `spatie/laravel-translatable` — translatable fields on Eloquent models
- `spatie/laravel-sitemap` — sitemap generation (must be added to `composer.json`)
- `SiteSetting` model — key-value store for global SEO defaults
- `SeoComposer` ViewComposer — already registered; extends global defaults resolution
- `App\Http\ViewComposers\SeoComposer` registered in `AppServiceProvider::boot()`
