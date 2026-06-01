## Verification Report

**Change**: seo-home-fixes  
**Version**: N/A  
**Mode**: Strict TDD  
**Artifact Mode**: openspec

### Completeness
| Metric | Value |
|--------|-------|
| Tasks total | 11 |
| Tasks checked complete | 0 |
| Tasks incomplete | 11 |

### Build & Tests Execution
**Build / Syntax**: ✅ Passed
```text
docker compose exec php php -l app/Http/View/Composers/SeoComposer.php
→ No syntax errors detected

docker compose exec php php -l app/Providers/AppServiceProvider.php
→ No syntax errors detected
```

**Static Analysis**: ❌ Failed / blocked
```text
docker compose exec php vendor/bin/phpstan analyse app/Http/View/Composers/SeoComposer.php --memory-limit=1G
→ OCI runtime exec failed: exec: "vendor/bin/phpstan": stat vendor/bin/phpstan: no such file or directory
```

**Tests**: ❌ Failed
```text
docker compose exec php php artisan test
→ 6 failed, 10 passed
→ failing suite: Tests\Unit\Domain\Offices\OfficeEntityTest
→ error: AGC\Domain\Offices\Entities\Office::__construct(): Argument #5 ($description) not passed
→ phpunit cache warning: permission denied writing .phpunit.result.cache
```

**Coverage**: ➖ Not available
```text
docker compose exec php php artisan test --coverage
→ ERROR  Code coverage driver not available. Did you install Xdebug or PCOV?
```

**Runtime evidence**
```text
curl http://localhost:8080/
→ rel="canonical": present, value = http://localhost:8080
→ og:title / og:description / og:type / og:url: present
→ og:image: absent
→ twitter:card / twitter:title / twitter:description: present
→ twitter:image: absent
→ application/ld+json in <head>: present
→ rendered JSON-LD types: AccountingService, WebSite
→ BreadcrumbList: absent
→ Leaflet CSS in <head>: present
→ Leaflet JS in <body>: present, not deferred

curl http://localhost:8080/?utm_source=test
→ canonical = http://localhost:8080 (query stripped)

curl http://localhost:8080/es
→ canonical = http://localhost:8080/es

curl -I http://localhost:8080/images/og-default.jpg
→ HTTP/1.1 404 Not Found
```

### TDD Compliance
| Check | Result | Details |
|-------|--------|---------|
| TDD evidence reported | ❌ | No `apply-progress` artifact found for this change |
| All tasks have tests | ❌ | No test files found for `seo-home-fixes` |
| RED confirmed (tests exist) | ❌ | No change-specific tests to verify |
| GREEN confirmed (tests pass) | ❌ | No change-specific tests to verify |
| Triangulation adequate | ❌ | No scenario coverage found |
| Safety Net for modified files | ❌ | No apply-progress evidence and full suite is red |

**TDD Compliance**: 0/6 checks passed

### Test Layer Distribution
| Layer | Tests | Files | Tools |
|-------|-------|-------|-------|
| Unit | 0 | 0 | Pest/PHPUnit available |
| Integration | 0 | 0 | Pest/PHPUnit available |
| E2E | 0 | 0 | Playwright prepared, not implemented |
| **Total** | **0** | **0** | |

### Changed File Coverage
| File | Line % | Branch % | Uncovered Lines | Rating |
|------|--------|----------|-----------------|--------|
| app/Http/View/Composers/SeoComposer.php | ➖ | ➖ | Coverage tool unavailable | ➖ |
| resources/views/public/components/schema.blade.php | ➖ | ➖ | Coverage tool unavailable | ➖ |
| app/Providers/AppServiceProvider.php | ➖ | ➖ | Coverage tool unavailable | ➖ |
| resources/views/layouts/public.blade.php | ➖ | ➖ | Coverage tool unavailable | ➖ |
| resources/views/public/home-sections/stats.blade.php | ➖ | ➖ | Coverage tool unavailable | ➖ |
| resources/views/public/home-sections/offices_map.blade.php | ➖ | ➖ | Coverage tool unavailable | ➖ |
| resources/lang/{ca,es,en}/messages.php | ➖ | ➖ | Coverage tool unavailable | ➖ |

**Average changed file coverage**: Coverage analysis skipped — no coverage driver detected

### Assertion Quality
**Assertion quality**: ⚠️ No change-specific tests exist, so there are no assertions proving this change's behavior.

### Quality Metrics
**Linter / Static Analysis**: ❌ PHPStan could not run because `vendor/bin/phpstan` is missing inside the php container  
**Type Checker**: ➖ N/A for this PHP project

### Spec Compliance Matrix
| Capability | Requirement / Scenario | Evidence | Result |
|-----------|-------------------------|----------|--------|
| seo-structured-data | `<head>` includes JSON-LD | `schema.blade.php` included in `layouts/public.blade.php`; runtime HTML contains JSON-LD | ✅ COMPLIANT |
| seo-structured-data | Home page contains Organization node | Runtime JSON-LD only contains `AccountingService` and `WebSite` | ❌ FAILING |
| seo-structured-data | Home page contains LocalBusiness node | No `LocalBusiness` node in composer or rendered HTML | ❌ FAILING |
| seo-structured-data | WebSite contains SearchAction | `getWebsiteSchema()` returns `SearchAction`; runtime confirms it renders | ✅ COMPLIANT |
| seo-structured-data | JSON encoding uses `JSON_UNESCAPED_UNICODE | JSON_HEX_TAG` | Partial uses `JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES`; no `JSON_HEX_TAG` | ❌ FAILING |
| seo-structured-data | Home page breadcrumb list renders | `getBreadcrumbSchema()` exists but is never added to `$schemas`; runtime HTML has no `BreadcrumbList` | ❌ FAILING |
| seo-structured-data | Inner page breadcrumb list renders | No breadcrumb composition logic found | ❌ UNTESTED |
| seo-structured-data | Empty schemas array renders nothing | Partial guards with `@if(!empty($schemas))` | ✅ COMPLIANT |
| seo-structured-data | Multiple schemas emit multiple script blocks | Runtime emits exactly 2 scripts, not the required minimum 3 sitewide schemas | ❌ FAILING |
| seo-structured-data | Composer survives missing `og_image` and still builds all sitewide schemas | Runtime survives missing image, but sitewide schemas are incomplete | ❌ FAILING |
| seo-social-meta | `og:title`, `og:description`, `og:type`, `og:url` present | Verified in rendered `<head>` | ✅ COMPLIANT |
| seo-social-meta | `og:image` present with configured/fallback source | Layout renders conditionally, but runtime value is absent because fallback asset is missing | ❌ FAILING |
| seo-social-meta | `og:image` conditional on non-empty `$ogImage` | `@if(!empty($ogImage))` implemented | ✅ COMPLIANT |
| seo-social-meta | `twitter:card`, `twitter:title`, `twitter:description` present | Verified in rendered `<head>` | ✅ COMPLIANT |
| seo-social-meta | `twitter:image` matches `og:image` | Both absent at runtime because `$ogImage` resolves to empty string | ❌ FAILING |
| seo-social-meta | Fallback chain `SiteSetting::get('og_image') -> asset('images/og-default.jpg') -> empty` | Method implements `SiteSetting -> file_exists(asset) -> empty`; fallback asset missing on disk | ❌ FAILING |
| seo-social-meta | OG default image asset accessible | `curl -I /images/og-default.jpg` returns 404 | ❌ FAILING |
| seo-canonical | Canonical tag present on public page | Runtime HTML contains one canonical link | ✅ COMPLIANT |
| seo-canonical | Canonical locale-aware for `/es` | Runtime `/es` canonical = `http://localhost:8080/es` | ✅ COMPLIANT |
| seo-canonical | Canonical strips query strings | Runtime `/?utm_source=test` canonical = `http://localhost:8080` | ✅ COMPLIANT |
| seo-canonical | Canonical resolved in `SeoComposer` via LaravelLocalization | `SeoComposer` never sets `$canonicalUrl`; layout uses `url()->current()` directly | ❌ FAILING |
| seo-canonical | Composer registered as `View::composer('*', SeoComposer::class)` in `boot()` | Registered in `boot()`, but only for `layouts.public` | ⚠️ PARTIAL |
| seo-canonical | Inner English page canonical validated | `curl http://localhost:8080/en/services` returns 404 in this environment | ❌ UNTESTED |
| seo-accessibility | Stats section has `aria-labelledby="stats-heading"` | Present in Blade and rendered HTML | ✅ COMPLIANT |
| seo-accessibility | Stats section has `<h2>` with `sr-only` | Present in Blade and rendered HTML | ✅ COMPLIANT |
| seo-accessibility | Heading text translated | Runtime CA heading = `AGC Assessors en xifres`; ES/EN translation keys exist | ✅ COMPLIANT |
| seo-accessibility | Spec key `messages.stats_heading` exists in all locales | Implementation uses `messages.stats.title`; `stats_heading` key is absent | ❌ FAILING |
| seo-accessibility | Heading hierarchy not broken | `<h2>` appears before stats item text and no evidence of broken hierarchy in source | ✅ COMPLIANT |
| seo-performance | Leaflet CSS/JS moved out of blocking path | CSS still rendered in `<head>`; JS lacks `defer` | ❌ FAILING |
| seo-performance | Layout exposes required stacks for deferred assets | Layout has `@stack('head')` and `@stack('scripts')`, not spec'd `@stack('styles')` | ⚠️ PARTIAL |
| seo-performance | Preconnect and dns-prefetch hints present for Google Fonts, unpkg, and OSM | Google Fonts preconnect present; unpkg/OSM only have dns-prefetch | ⚠️ PARTIAL |
| seo-performance | Preconnect hints appear before font stylesheet links | Google Fonts preconnects appear before font links | ✅ COMPLIANT |
| seo-performance | Map initialises only after deferred Leaflet load | Init script calls `L.map()` directly on `DOMContentLoaded`; no load/defer guard | ❌ FAILING |
| seo-performance | IntersectionObserver ensures one-time map init | No `IntersectionObserver` or one-time guard exists | ❌ FAILING |

**Compliance summary**: 12/34 rows compliant, 3 partial, 16 failing, 3 untested

### Correctness (Static Evidence)
| Requirement | Status | Notes |
|------------|--------|-------|
| SeoComposer returns AccountingService-like schema | ⚠️ Implemented with deviation | Returns `AccountingService`, but spec requires `Organization` and `LocalBusiness` nodes as well |
| `getWebsiteSchema()` includes `SearchAction` | ✅ Implemented | Present in code and rendered output |
| `getBreadcrumbSchema(array $items)` exists | ⚠️ Implemented with deviation | Method exists, but never used in `compose()` |
| Schema partial renders only when non-empty | ✅ Implemented | Blade guard present |
| Layout includes schema partial in `<head>` | ✅ Implemented | Included before `@stack('head')` |
| Social meta core tags | ✅ Implemented | Except image tags fail at runtime due missing fallback asset |
| Canonical link output | ⚠️ Implemented with deviation | Works via `url()->current()`, not via composer + LaravelLocalization contract |
| Accessibility heading | ✅ Implemented | `sr-only` + `aria-labelledby` both present |
| Translation additions | ⚠️ Implemented with deviation | Added `messages.stats.title`, not spec'd `messages.stats_heading` |
| Deferred Leaflet loading | ❌ Not implemented per spec | CSS still in head, JS not deferred, no visibility guard |

### Coherence (Design)
| Decision | Followed? | Notes |
|----------|-----------|-------|
| Composer registered in `AppServiceProvider::boot()` | ✅ Yes | Registered in `boot()` |
| Composer should provide canonical + breadcrumbs + schemas + ogImage | ❌ No | Only `schemas` and `ogImage` are provided |
| Canonical should use `LaravelLocalization::getLocalizedURL(...)` | ❌ No | Layout uses `url()->current()` |
| Base layout should consume composer canonical | ❌ No | Layout still supports `@section('seo_canonical')` fallback pattern |
| Schema partial should use `JSON_UNESCAPED_UNICODE | JSON_HEX_TAG` | ❌ No | Uses different flags |
| Leaflet should be deferred with observer guard | ❌ No | No defer, no observer, no one-time init guard |
| Default OG image should exist in `public/images/og-default.jpg` | ❌ No | File missing; runtime returns 404 |

### Issues Found
**CRITICAL**
- Missing required sitewide schema coverage: runtime HTML renders `AccountingService` and `WebSite`, but no `Organization`, no `LocalBusiness`, and no `BreadcrumbList`.
- `SeoComposer::compose()` never composes `$canonicalUrl` or `$breadcrumbs`, so two required capabilities are not actually driven by the composer.
- Schema JSON encoding does not use `JSON_HEX_TAG`, which the spec explicitly requires for safe JSON-LD emission.
- `public/images/og-default.jpg` is missing; `curl -I http://localhost:8080/images/og-default.jpg` returns 404, so `og:image` and `twitter:image` are absent at runtime.
- Full test suite is red (`6 failed, 10 passed`), so the change cannot be considered verified under Strict TDD.
- No change-specific tests exist for any `seo-home-fixes` scenario; every spec scenario lacks runtime test coverage.
- No `apply-progress` artifact / TDD cycle evidence was found even though `strict_tdd: true` is enabled in `openspec/config.yaml`.
- PHPStan target verification could not run because `vendor/bin/phpstan` is missing inside the php container, so task 4.1 is not satisfiable in the current environment.
- Leaflet CSS remains in the blocking `<head>` path, JS is not deferred, and the map init has no `IntersectionObserver`/single-init guard.

**WARNING**
- Canonical behavior is correct on `/` and `/es`, and query strings are stripped, but implementation deviates from the spec by using `url()->current()` in the layout instead of composer-driven `LaravelLocalization` resolution.
- `AppServiceProvider` registers the composer only for `layouts.public`, not `View::composer('*', SeoComposer::class)` as specified.
- Accessibility behavior works, but the implementation changed the translation contract from `messages.stats_heading` to `messages.stats.title`, diverging from the spec artifact.
- `getWebsiteSchema()` builds `urlTemplate` as `http://localhost:8080/ca?q={search_term_string}`; default locale spec/examples expect the root locale without `/ca`, and task text expected `?s=`.
- Layout uses `@stack('head')` instead of the spec'd `@stack('styles')`, which is why Leaflet CSS is still emitted in `<head>`.
- Resource hints are only partial for third-party map origins: unpkg and OSM get `dns-prefetch`, but not `preconnect`.
- `curl http://localhost:8080/en/services` returned 404, so the canonical inner-page example from the spec could not be validated in this environment.
- Tasks artifact was not updated at all (0/11 checked), so artifact completeness does not match the user's claim that the implementation is complete.

**SUGGESTION**
- `AppServiceProvider.php` does not include `declare(strict_types=1);`, which violates the project convention stated in AGENTS.md.
- If `AccountingService` is the intentional business type, update the OpenSpec capability so the required node set is unambiguous; right now spec and implementation are describing different schema contracts.

### Severity Summary
| Severity | Count |
|----------|-------|
| CRITICAL | 9 |
| WARNING | 8 |
| SUGGESTION | 2 |

### Verdict
FAIL

The change is **not verified**. Core spec requirements are missing at runtime (structured data coverage, social image fallback, deferred map loading), Strict TDD evidence is absent, PHPStan validation could not run, and the full test suite is currently red.
