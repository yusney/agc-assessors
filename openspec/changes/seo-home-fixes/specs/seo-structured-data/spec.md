# seo-structured-data Specification

## Purpose

Inject Schema.org JSON-LD blocks (`Organization`, `LocalBusiness`, `WebSite` with `SearchAction`,
and per-page `BreadcrumbList`) into every public page via a dedicated Blade partial and a
ViewComposer. Enables Google rich snippets and Knowledge Panel for the firm.

---

## Requirements

### Requirement: JSON-LD Partial Rendered in `<head>`

The system MUST render a `<script type="application/ld+json">` block in `<head>` on every public
page containing at minimum the `Organization`, `LocalBusiness`, and `WebSite` schemas.

#### Scenario: Home page — sitewide schemas present

- GIVEN a visitor requests the home page in any locale
- WHEN the HTML response is returned
- THEN `<head>` MUST contain a `<script type="application/ld+json">` element
- AND the JSON MUST include a node with `"@type": "Organization"`
- AND the JSON MUST include a node with `"@type": "LocalBusiness"`
- AND the JSON MUST include a node with `"@type": "WebSite"` with a `potentialAction.SearchAction`

#### Scenario: JSON encodes non-ASCII characters safely

- GIVEN the firm name or address contains Catalan/Spanish characters (e.g., "Assessors")
- WHEN `json_encode` runs
- THEN the output MUST use `JSON_UNESCAPED_UNICODE | JSON_HEX_TAG` flags
- AND the resulting JSON MUST be valid UTF-8 parseable by `json_decode`

---

### Requirement: BreadcrumbList Per Route

The system MUST inject a `BreadcrumbList` JSON-LD node on every public page; on the home page
the list MUST contain exactly one item (the home/root URL).

#### Scenario: Home page breadcrumb

- GIVEN a visitor requests `/` (Catalan default locale)
- WHEN `SeoComposer` composes the view
- THEN `$breadcrumbs` MUST be `[['name' => 'Inici', 'url' => 'https://agcassessors.com/']]`
- AND the rendered JSON-LD MUST contain `"@type": "BreadcrumbList"` with `itemListElement[0].position === 1`

#### Scenario: Inner page breadcrumb

- GIVEN a visitor requests `/es/servicios`
- WHEN `SeoComposer` composes the view
- THEN `$breadcrumbs` MUST contain two items: home (position 1) and the current page (position 2)
- AND each item MUST have `"@type": "ListItem"`, `position`, `name`, and `item` (URL) fields

---

### Requirement: Schema Partial Receives `$schemas` Array

The Blade partial `public/components/schema.blade.php` MUST accept a `$schemas` variable
(array of schema arrays) passed by `SeoComposer` and render one `<script>` per item.

#### Scenario: Empty schemas array — no output

- GIVEN `$schemas` is an empty array
- WHEN the partial renders
- THEN no `<script type="application/ld+json">` MUST appear in the output

#### Scenario: Multiple schemas — multiple script blocks

- GIVEN `$schemas` contains three arrays (Organization, LocalBusiness, WebSite)
- WHEN the partial renders
- THEN exactly three `<script type="application/ld+json">` elements MUST be emitted

---

### Requirement: SeoComposer Builds Schema Arrays Without Framework Side-effects

`App\Http\ViewComposers\SeoComposer` MUST build schema arrays as plain PHP arrays.
It MUST NOT perform HTTP calls, queue jobs, or write to the database during view composition.

#### Scenario: SeoComposer composes successfully when SiteSetting has no `og_image`

- GIVEN `SiteSetting::get('og_image')` returns `null`
- WHEN `SeoComposer::compose()` runs
- THEN the composer MUST NOT throw; it MUST fall back to `asset('images/og-default.jpg')`
- AND the composed `$schemas` MUST still include all sitewide schema nodes

---

## Validation

| Check | Method |
|---|---|
| `@type Organization` present in HTML | `curl -s https://agcassessors.com/ \| grep -o '"@type":"Organization"'` |
| Valid JSON-LD | Google Rich Results Test → home URL |
| PHPStan level 8 passes | `docker compose exec php vendor/bin/phpstan analyse app/Http/ViewComposers/SeoComposer.php` |
| `json_decode` roundtrip | Unit test on `SeoComposer::buildSchemas()` output |

---

## Dependencies

- `seo-canonical` — `SeoComposer` is shared; canonical URL is resolved in the same composer
- `SiteSetting` model with `get(string $key): ?string` helper
- `mcamara/laravel-localization` — for locale-aware `@id` URLs in schemas
- `public/images/og-default.jpg` committed at 1200×630 px
