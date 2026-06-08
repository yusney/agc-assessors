# Tasks: SEO Multilingual Metadata

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~470 additions + ~40 deletions (net +430) |
| Files affected | 11 (3 new, 6 modified, 2 test files) |
| Chained PRs recommended | Yes |
| Suggested split | PR1 → PR2 → PR3 |
| Delivery strategy | ask-always |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: pending
400-line budget risk: High

### Chain Strategy Options (choose one before apply)
- **Stacked PRs to main**: PR1→develop merge, rebase PR2, merge, rebase PR3, merge. Simple.
- **Feature Branch Chain**: tracker `feat/seo-multilingual-metadata` accumulates; PR1 targets tracker, PR2 targets PR1 branch, PR3 targets PR2 branch. Only tracker merges to develop. Better rollback.
- **size:exception**: Single PR (~430 net). Requires maintainer approval. NOT recommended.

### Recommended Commit Messages
| PR | Message |
|----|---------|
| PR1 | `fix(seo): add hreflang alternates, og:locale tags, fix services.show title source, global defaults fallback` |
| PR2 | `feat(admin): add SeoSettingsPage for per-locale SEO defaults` |
| PR3 | `feat(seo): add multilingual XML sitemap and verification tests` |

## Pre-Implementation Baseline

```bash
docker compose exec php php artisan test 2>&1 | tee tests-baseline.log
```
Document any pre-existing unrelated failures. Do NOT fix them in this change.

## PR1 — Foundation & Head Rendering (est. ~180 lines)

- [x] 1.1 **Fix `resources/views/public/services/show.blade.php`** — L3: replace `$service->name()` with `$service->seo()->title()->get(app()->getLocale()) ?: $service->name()`; L4: use `seo()->description()` first, fallback `strip_tags($description)[0..159]`
  - Verify: `curl -s localhost:8080/serveis/{slug} | grep '<title>'`
  - Rollback: `git checkout -- resources/views/public/services/show.blade.php`

- [x] 1.2 **New `resources/views/public/partials/seo.blade.php`** — hreflang loop over `getSupportedLocales()`, `og:locale`+`og:locale:alternate`, twitter:card/title/description mirror, canonical from SeoComposer
  - Verify: `curl -s localhost:8080/ | grep 'hreflang' | wc -l` → 4; `grep 'og:locale'`
  - Rollback: `rm resources/views/public/partials/seo.blade.php`

- [x] 1.3 **Modify `resources/views/layouts/public.blade.php`** — replace inline OG/canonical with `@include('public.partials.seo', $seoData)`
  - Rollback: `git checkout -- resources/views/layouts/public.blade.php`

- [x] 1.4 **Modify `app/Http/View/Composers/SeoComposer.php`** — add `getHreflangAlternates(): array`, `getOgLocaleAlternates(): array`, `getGlobalDefaultTitle(?string $locale): ?string`, `getGlobalDefaultDescription(?string $locale): ?string`; read from `SiteSetting::get('seo.global.{locale}.{field}')`; bind all to view
  - Verify: `docker compose exec php vendor/bin/phpstan analyse --level=8 app/Http/View/Composers/SeoComposer.php`
  - Rollback: `git checkout -- app/Http/View/Composers/SeoComposer.php`

- [x] 1.5 ~~**Modify `public/robots.txt`** — append `Sitemap: {{ config('app.url') }}/sitemap.xml`~~
  - **Reverted in PR1 follow-up**: sitemap.xml does not exist until PR3 (task 3.1). Adding the directive before the route exists is misleading to crawlers. Sitemap line removed from robots.txt; deferred to PR3 alongside SitemapController.
  - See task 3.3 (robots.txt Sitemap line, moved from 3.5 below)

- [x] 1.6 **New `tests/Feature/Http/SeoHeadTest.php`** — Pest: assert hreflang×4 on `/`, og:locale+alternates on `/`, `services.show` `<title>` from `seo_title`
  - Verify: `docker compose exec php php artisan test --filter SeoHeadTest`
  - Rollback: `rm tests/Feature/Http/SeoHeadTest.php`
  - **Follow-up (PR1 review)**: Added `test_home_route_slash_renders_canonical_hreflang_og_locale_no_keywords` using `$this->view('public.pages.home', [...])` because the outer `GET /` route always redirects (routing architecture constraint). Also fixed `services/show.blade.php` sections to shorthand form and layout `{{ }}` → `{!! !!}` to prevent latent double-encoding. 15/15 feature + 11/11 unit tests green.

## PR2 — Admin Global Defaults (est. ~170 lines)

- [x] 2.1 **New `src/Filament/Pages/SeoSettingsPage.php`** — Filament 5 page with `Schema`+Tabs for ca/es/en; fields: `TextInput` title, `Textarea` description per locale, single `TextInput` og_image (URL, not FileUpload); save to `SiteSetting` keys `seo.global.{locale}.{field}` + `seo.global.og_image`; registered in `AdminPanelProvider`. NO keywords field.
  - Blade view: `resources/views/filament/pages/seo-settings.blade.php`
  - Note: og_image implemented as `TextInput::make('og_image')->url()` (not FileUpload — simpler, no Curator dependency)

- [x] 2.2 **Modify `app/Http/View/Composers/SeoComposer.php`** — `getOgImage()` reads `seo.global.og_image` first (falls back to legacy `og_image` key); `compose()` passes `globalDefaultTitle` and `globalDefaultDescription` to view; layout uses them as fallback when entity seo_title/seo_description is empty.

- [x] 2.3 **Extend `tests/Feature/Http/SeoHeadTest.php`** + **New `tests/Feature/Filament/SeoSettingsPageTest.php`** — schema inspection (7 fields, no keywords, per-locale title/desc, shared og_image), save persists to SiteSetting keys, HTTP smoke 200 for admin, global default fallback via `pages.show` route (not services.show which always has name() fallback), entity priority triangulation.
  - Note: Schema inspection uses PHP Reflection instead of `getFlatComponents()` (which needs Livewire context); `getChildComponents['default']` for Component objects, `$components` for Schema objects.
  - Test for global default uses `pages.show` with Page(seo_title=null, title='') so `@section('seo_title', '' ?: '')` yields empty → layout fallback chain triggers.

## PR3 — Sitemap & Verification (est. ~150 lines)

- [x] 3.1 **New `app/Http/Controllers/Public/SitemapController.php`** — `index()`: query active News/Pages/Services; for each emit `<url><loc>`×3 locales via `getLocalizedURL()`; add static routes (home, offices, team, contact, search)×3 locales; return `response($xml, 200, ['Content-Type' => 'application/xml'])`
  - Verify: `docker compose exec php vendor/bin/phpstan analyse --level=8 app/Http/Controllers/Public/SitemapController.php`
  - Rollback: `rm app/Http/Controllers/Public/SitemapController.php`

- [x] 3.2 **Modify `routes/web.php`** — add `Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap')` OUTSIDE locale middleware group
  - Rollback: `git checkout -- routes/web.php`

- [x] 3.3 **Modify `public/robots.txt`** — add `Sitemap: {{ config('app.url') }}/sitemap.xml` once the sitemap route (3.2) is in place
  - Deferred from PR1 task 1.5: the directive must not exist before the URL it points to does.
  - Rollback: `git checkout -- public/robots.txt`

- [x] 3.4 **New `tests/Feature/Http/SitemapTest.php`** — assert HTTP 200, `application/xml` Content-Type, known slug in ca/es/en, unpublished excluded
  - Verify: `docker compose exec php php artisan test --filter SitemapTest`

- [x] 3.5 **Extend `tests/Feature/Http/SeoHeadTest.php`** — add: `services.show` title matches `seo_title`; `news.show` uses entity `seo_description`; robots.txt `Sitemap:` present (after task 3.3)

- [x] 3.6 **Full verification** — `docker compose exec php php artisan test`; `curl -s localhost:8080/sitemap.xml | head -5`; `curl -s localhost:8080/robots.txt | grep Sitemap`

## Keyword Scope Confirmation

Per spec non-goals and design decision D4: **NO meta keywords tasks included.** No `seo_keywords` migration, no model `$translatable` changes, no `<meta name="keywords">` tag, no keywords field in SeoSettingsPage or any Filament resource. The `SEOData::keywords` field remains as-is in the domain value object (dead code, preserved for future providers).
