# Proposal: SEO Fixes — Public Frontend

## Intent

The AGC Assessors public site lacks structured data, social sharing metadata, and
minor accessibility/performance gaps that prevent Google from identifying it as a
professional services firm and generating rich snippets. This change adds the
missing SEO layer across the entire public frontend without touching the admin
panel or the existing design.

## Scope

### In Scope
- Add `og:image`, Twitter Card meta tags, and `<link rel="canonical">` to base layout
- Inject Schema.org JSON-LD: `Organization`, `LocalBusiness`, `WebSite` (with `SearchAction`)
- Add per-page `BreadcrumbList` JSON-LD via a Blade partial + ViewComposer
- Create `resources/views/public/components/schema.blade.php` partial
- Add visually-hidden `<h2>` to stats section for screen-reader landmark continuity
- Async/defer Leaflet CSS+JS loading (`offices_map.blade.php`) to unblock first paint
- Default OG image at `public/images/og-default.jpg`; configurable via `SiteSetting::get('og_image')`
- ViewComposer (`SeoComposer`) that resolves canonical URL and breadcrumb data per route

### Out of Scope
- XML sitemap generation (separate change)
- Hreflang `<link>` tags (already partially handled; full audit deferred)
- Admin UI for structured data editing beyond `og_image` SiteSetting
- Performance audit (Core Web Vitals, image optimisation)
- Any page other than the public frontend (Filament panel untouched)

## Capabilities

### New Capabilities
- `seo-structured-data`: Schema.org JSON-LD injection (Organization, LocalBusiness, WebSite, BreadcrumbList) via Blade partial and ViewComposer
- `seo-social-meta`: og:image, og:type, Twitter Card meta tags in base layout with SiteSetting fallback
- `seo-canonical`: Canonical URL tag resolved per route via ViewComposer using `LaravelLocalization`

### Modified Capabilities
None — no existing spec-level behaviour changes; this is net-new SEO infrastructure.

## Approach

1. **ViewComposer** (`App\Http\ViewComposers\SeoComposer`, `final class`) — registered in
   `AppServiceProvider`. Resolves `$canonicalUrl` and `$breadcrumbs[]` for every public view.
   Uses `LaravelLocalization::getLocalizedURL()` for locale-aware canonical.

2. **Blade partial** `resources/views/public/components/schema.blade.php` — renders JSON-LD
   `<script type="application/ld+json">` blocks. Receives `$schemas` array from the composer.
   Schemas built as plain PHP arrays, `json_encode`d with `JSON_UNESCAPED_UNICODE`.

3. **Base layout** `resources/views/layouts/public.blade.php` — add `og:image`, `og:type`,
   `twitter:card`, `twitter:image`, `<link rel="canonical">`, and `@include` the schema partial.

4. **Stats section** — wrap existing content with `<h2 class="sr-only">` using the
   existing translation key (or a new one: `messages.stats_heading`).

5. **Leaflet deferral** — move `<link>` and `<script>` for Leaflet into a
   `@push('scripts')` / `@push('styles')` stack rendered at the bottom of the layout.
   Add `loading="lazy"` to the map container with an `IntersectionObserver` init guard.

6. **OG image** — commit `public/images/og-default.jpg` (1200×630 px). `SiteSetting::get('og_image')`
   takes precedence; falls back to `asset('images/og-default.jpg')`.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `resources/views/layouts/public.blade.php` | Modified | og:image, Twitter Cards, canonical, schema include |
| `resources/views/public/components/schema.blade.php` | New | JSON-LD partial |
| `resources/views/public/home-sections/stats.blade.php` | Modified | sr-only h2 heading |
| `resources/views/public/home-sections/offices_map.blade.php` | Modified | Leaflet async loading |
| `app/Http/ViewComposers/SeoComposer.php` | New | Canonical + breadcrumb resolver |
| `app/Providers/AppServiceProvider.php` | Modified | Register SeoComposer |
| `resources/lang/{ca,es,en}/messages.php` | Modified | Add `stats_heading` key |
| `public/images/og-default.jpg` | New | Default OG share image |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| JSON-LD malformed on non-UTF-8 chars | Low | `JSON_UNESCAPED_UNICODE \| JSON_HEX_TAG` flags |
| `SiteSetting` model not yet seeded with `og_image` key | Med | Null-safe fallback to hardcoded asset path |
| Leaflet deferral breaks map init timing | Med | Wrap `L.map()` call in `DOMContentLoaded` + guard |
| ViewComposer registered too early (before locale middleware) | Low | Register in `boot()`, not `register()`; locale is set by middleware before view render |

## Rollback Plan

All changes are additive or isolated to Blade views and one provider registration.
Rollback = revert the 5–6 files via `git revert`. No migrations, no DB changes,
no schema alterations required.

## Dependencies

- `mcamara/laravel-localization` — already installed (used for `getLocalizedURL`)
- `SiteSetting` Eloquent model — must expose `get(string $key): ?string` (check if exists or create a simple helper)
- OG default image asset (1200×630 jpg) — must be committed to `public/images/`

## Success Criteria

- [ ] Google Rich Results Test shows `Organization` + `WebSite` schemas on home page
- [ ] Social share preview (Facebook Debugger / Twitter Card Validator) shows image
- [ ] `<link rel="canonical">` present on every public page with correct locale URL
- [ ] Lighthouse SEO score ≥ 95 on home page (currently untracked)
- [ ] `<h2 class="sr-only">` present in stats section (passes axe-core audit)
- [ ] Leaflet CSS/JS absent from `<head>`; loaded asynchronously after DOM ready
- [ ] PHPStan level 8 passes with zero new errors on `SeoComposer`
- [ ] All existing tests green (`make test`)
