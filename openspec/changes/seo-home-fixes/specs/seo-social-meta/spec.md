# seo-social-meta Specification

## Purpose

Add `og:image`, `og:type`, `twitter:card`, and `twitter:image` meta tags to the base public
layout so social platforms (Facebook, Twitter/X, LinkedIn, WhatsApp) render a rich preview
with the firm's branding image when any page URL is shared.

---

## Requirements

### Requirement: Open Graph Image Meta Tag

The system MUST render `<meta property="og:image">` in `<head>` on every public page.
The value MUST be the absolute URL of the OG image resolved with the following priority:

1. `SiteSetting::get('og_image')` — admin-configurable value
2. `asset('images/og-default.jpg')` — hardcoded fallback

#### Scenario: SiteSetting has a custom og_image URL

- GIVEN `SiteSetting::get('og_image')` returns `'https://cdn.example.com/custom.jpg'`
- WHEN the layout renders
- THEN `<meta property="og:image" content="https://cdn.example.com/custom.jpg">` MUST be present
- AND `<meta property="og:image:width" content="1200">` and `og:image:height` SHOULD be present

#### Scenario: SiteSetting has no og_image key (null / missing)

- GIVEN `SiteSetting::get('og_image')` returns `null`
- WHEN the layout renders
- THEN `<meta property="og:image">` MUST use `asset('images/og-default.jpg')` as content
- AND no PHP warning or exception MUST occur

---

### Requirement: Open Graph Type Tag

The system MUST render `<meta property="og:type" content="website">` on all public pages.

#### Scenario: og:type always present

- GIVEN any public page request
- WHEN the HTML `<head>` is inspected
- THEN `<meta property="og:type" content="website">` MUST appear exactly once

---

### Requirement: Twitter Card Meta Tags

The system MUST render the following Twitter Card tags:

| Tag | Value |
|---|---|
| `twitter:card` | `summary_large_image` |
| `twitter:image` | Same resolved URL as `og:image` |

#### Scenario: Twitter card renders with default image

- GIVEN `SiteSetting::get('og_image')` returns `null`
- WHEN the layout renders
- THEN `<meta name="twitter:card" content="summary_large_image">` MUST be present
- AND `<meta name="twitter:image">` content MUST equal the `og:image` fallback URL

#### Scenario: Twitter card renders with admin-configured image

- GIVEN `SiteSetting::get('og_image')` returns a valid URL
- WHEN the layout renders
- THEN `twitter:image` MUST match `og:image` exactly (same source)

---

### Requirement: OG Default Image Asset Committed

`public/images/og-default.jpg` MUST exist in the repository at exactly 1200×630 px,
weighing under 200 KB, and represent the firm's visual identity.

#### Scenario: Asset accessible via browser

- GIVEN the application is running
- WHEN a browser requests `/images/og-default.jpg`
- THEN the server MUST return HTTP 200 with `Content-Type: image/jpeg`

---

## Validation

| Check | Method |
|---|---|
| og:image present | `curl -s https://agcassessors.com/ \| grep 'og:image'` |
| Facebook preview | Facebook Sharing Debugger → scrape home URL |
| Twitter preview | Twitter Card Validator → enter home URL |
| Fallback works | Temporarily unset `og_image` SiteSetting; verify asset URL in HTML |
| Image dimensions | `identify public/images/og-default.jpg` (ImageMagick) |

---

## Dependencies

- `SiteSetting` model with `get(string $key): ?string`
- `public/images/og-default.jpg` — 1200×630 px committed asset
- `seo-structured-data` — `SeoComposer` also resolves the OG image URL for schemas
