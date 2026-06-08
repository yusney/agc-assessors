# Exploration: SEO Multilingual Metadata

## Goal
Audit whether the app supports SEO best practices from the backend for every public content type: services, pages, news/articles, sections, offices, and team members — with particular focus on per-language SEO title, description, keywords, and related tags.

---

## Current State

### Database Schema — SEO Fields per Content Type

| Content Type | `seo_title` (JSON) | `seo_description` (JSON) | `seo_canonical` | `keywords` | OG fields | `robots` |
|---|---|---|---|---|---|---|
| `news_articles` | ✅ | ✅ | ✅ string | ❌ | ❌ | ❌ |
| `pages` | ✅ | ✅ | ✅ string | ❌ | ❌ | ❌ |
| `services` | ✅ | ✅ | ✅ string | ❌ | ❌ | ❌ |
| `home_sections` | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `offices` | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `team_members` | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

### SEOData Value Object (`src/Domain/Shared/ValueObjects/SEOData.php`)
- Has `title` (TranslatableString), `description` (TranslatableString), `canonicalUrl` (?string), `keywords` (array)
- **keywords** is NEVER persisted to DB — no column exists, no repository populates it, no form collects it

### Repositories
- `EloquentNewsRepository`, `EloquentPageRepository`, `EloquentServiceRepository`: map `seo_title`, `seo_description`, `seo_canonical` to SEOData but skip `keywords`
- `EloquentOfficeRepository`, `EloquentTeamMemberRepository`: no SEO mapping at all

### Domain Entities
| Entity | Has SEOData |
|---|---|
| `NewsArticle` | ✅ |
| `Page` | ✅ |
| `Service` | ✅ |
| `Office` | ❌ |
| `TeamMember` | ❌ |
| (HomeSection is not a domain entity — used directly) | ❌ |

### Filament Resources — SEO Admin Forms
| Resource | SEO Section |
|---|---|
| `NewsResource` | ✅ per-language seo_title/seo_description + canonical |
| `PageResource` | ✅ per-language seo_title/seo_description + canonical |
| `ServiceResource` | ✅ per-language seo_title/seo_description + canonical |
| `OfficeResource` | ❌ |
| `HomeSectionResource` | ❌ |
| `TeamMemberResource` | ❌ (not inspected but model has no SEO fields) |

No Filament settings page exists for global SEO defaults (meta title/description for the site).

### View Layer — How SEO Renders

**Layout** (`resources/views/layouts/public.blade.php`):
- `<title>` via `@yield('seo_title', config('app.name'))`
- `<meta name="description">` via `@yield('seo_description', '')`
- `<link rel="canonical">` via `@yield('seo_canonical')` or fallback to `$canonicalUrl`
- `<meta property="og:title/description/type/url/image">` mirror the yielded values
- `<meta name="twitter:card/title/description/image">` mirror
- `<meta name="robots">` hardcoded: `index, follow`
- **No hreflang alternate links** anywhere
- **No keywords meta tag** output

**Per-page view analysis**:
| View | seo_title source | seo_description source | Uses SEOData? |
|---|---|---|---|
| `news.show` | `$article->seo()->title()` → fallback `$article->title()` | `$article->seo()->description()` | ✅ |
| `news.index` | `__('messages.news.seo_title')` | `__('messages.news.seo_description')` | N/A (index page) |
| `services.show` | `$service->name() . ' – AGC Assessors'` | `strip_tags($service->description())` | ❌ — ignores SEOData! |
| `services.index` | `__('messages.services.seo_title')` | `__('messages.services.seo_description')` | N/A |
| `pages.show` | `$page->seo_title` → `$page->title` (via Eloquent directly) | `$page->seo_description` | ✅ (via Eloquent model) |
| `home` | `__('messages.home.seo_title')` | `__('messages.home.seo_description')` | N/A |
| `offices.index` | `__('messages.offices.title') . ' – AGC Assessors'` | `__('messages.offices.subtitle')` | N/A (no SEO data per office) |
| `team.index` | `__('messages.team.seo_title')` | `__('messages.team.seo_description')` | N/A |
| `contact.index` | `__('messages.contact.seo_title')` | `__('messages.contact.seo_description')` | N/A |
| `search.index` | `__('messages.search.title') . ' – AGC Assessors'` | `__('messages.search.title')` | N/A |
| `work-with-us` | `$settings['seo_title'][$locale]` fallback `__('messages.careers.seo_title')` | `$settings['seo_description'][$locale]` fallback `__('messages.careers.seo_description')` | Via SiteSetting |

### Hreflang / Locale Alternates
- **Completely missing** — no `<link rel="alternate" hreflang="..."` in layout or anywhere
- Locale switching works via `route('locale.switch', $locale)` but without SEO alternate links
- The `SeoComposer` generates canonical URLs with `LaravelLocalization::getLocalizedURL()` but only for the current locale

### JSON-LD / Structured Data
- ✅ `SeoComposer` generates Organization, LocalBusiness, Website schemas on every page
- ✅ Breadcrumb schema when breadcrumbs array is provided
- Rendered in layout via `public.components.schema` partial

### Sitemap & Robots
- `robots.txt` exists at `public/robots.txt` — minimal (`User-agent: * / Disallow:`)
- **No sitemap.xml** — `spatie/laravel-sitemap` is NOT installed (not in composer.json dependencies)
- No sitemap route or generator

### Global SEO Defaults
- No Filament settings page for global meta title/description/keywords/og_image
- `SiteSetting` key-value store has `og_image` and `logo_url` used by SeoComposer
- Translation keys (`messages.*.seo_title`, `messages.*.seo_description`) serve as static defaults for index/list pages
- `config('app.name')` is used as final fallback in layout

---

## Gaps & Risks

1. **Keywords**: Exists in SEOData VO but orphaned — no DB column, no form, no view output. Useless.
2. **Services detail page ignores SEOData**: Uses content title instead of SEO title. The `services.show` Blade (@section line 3-4) reads `$service->name()` and `$service->description()` instead of `$service->seo()->title()` and `$service->seo()->description()`.
3. **Offices**: Zero SEO support — no DB columns, no Filament form, no domain entity field, no view rendering.
4. **Team Members**: Zero SEO support.
5. **Home Sections**: Zero SEO support (though arguably they are part of the homepage which has static translation-based SEO).
6. **Hreflang alternate links**: Missing entirely — critical for multilingual SEO and Google understanding language/region targeting.
7. **No sitemap.xml**: No package installed, no route, no generator.
8. **No global SEO defaults admin UI**: Editors cannot set site-wide meta title/description/og:image from Filament.
9. **No `og:locale` / `og:locale:alternate` tags**: OpenGraph lacks locale alternates.
10. **robots.txt bare minimum**: No sitemap reference.
11. **Keywords meta tag never rendered**: Even if data were added, the layout doesn't output `<meta name="keywords">`.

---

## Recommended Scope for Proposal

### Must Have
1. **Add `seo_keywords` JSON column** to `news_articles`, `pages`, `services` (populate `keywords` in SEOData)
2. **Add translatable SEO fields** (`seo_title`, `seo_description`, optionally `seo_keywords`) to `offices`, `team_members`, `home_sections`
3. **Fix `services.show`** to use `$service->seo()->title()` / `$service->seo()->description()` with fallback
4. **Add hreflang alternate links** to layout — loop `LaravelLocalization::getSupportedLocales()` and output `<link rel="alternate" hreflang="xx" href="...">`
5. **Add sitemap package** (`spatie/laravel-sitemap`) and configure
6. **Add global SEO defaults Filament page** — meta title, meta description, og:image, keywords (editable per locale)
7. **Add keywords meta tag** to layout if present

### Nice to Have
- `og:locale` / `og:locale:alternate` OpenGraph tags
- Individual `og:image` override per content item (stored as media relationship)
- `noindex`/`nofollow` toggle per page (robots field)
- Per-service/per-office detail pages (currently `offices` has no show route, offices.index is the only page)

---

## Exact Files Likely Involved

### Migrations (new files)
- `database/migrations/XXXX_XX_XX_XXXXXX_add_seo_fields_to_offices.php`
- `database/migrations/XXXX_XX_XX_XXXXXX_add_seo_fields_to_team_members.php`
- `database/migrations/XXXX_XX_XX_XXXXXX_add_seo_keywords_to_content_tables.php`

### Domain
- `src/Domain/Offices/Entities/Office.php` — add SEOData property
- `src/Domain/Team/Entities/TeamMember.php` — add SEOData property
- `src/Domain/Shared/ValueObjects/SEOData.php` — (already has `keywords`, no change needed)
- `src/Domain/News/Entities/NewsArticle.php` — (already has SEOData)
- `src/Domain/Page/Entities/Page.php` — (already has SEOData)
- `src/Domain/Service/Entities/Service.php` — (already has SEOData)

### Infrastructure
- `src/Infrastructure/Persistence/Eloquent/Models/EloquentOffice.php` — add translatable SEO fields
- `src/Infrastructure/Persistence/Eloquent/Models/TeamMemberModel.php` — add translatable SEO fields
- `src/Infrastructure/Persistence/Eloquent/Models/HomeSection.php` — maybe add SEO fields
- `src/Infrastructure/Persistence/Eloquent/Models/NewsModel.php` — add `seo_keywords` to translatable
- `src/Infrastructure/Persistence/Eloquent/Models/PageModel.php` — add `seo_keywords` to translatable
- `src/Infrastructure/Persistence/Eloquent/Models/ServiceModel.php` — add `seo_keywords` to translatable
- `src/Infrastructure/Persistence/Eloquent/Repositories/EloquentOfficeRepository.php` — map SEOData
- `src/Infrastructure/Persistence/Eloquent/Repositories/EloquentTeamMemberRepository.php` — map SEOData
- `src/Infrastructure/Persistence/Eloquent/Repositories/EloquentNewsRepository.php` — add keywords mapping
- `src/Infrastructure/Persistence/Eloquent/Repositories/EloquentPageRepository.php` — add keywords mapping
- `src/Infrastructure/Persistence/Eloquent/Repositories/EloquentServiceRepository.php` — add keywords mapping

### Filament (Admin)
- `src/Filament/Resources/OfficeResource.php` — add SEO section with per-language fields
- `src/Filament/Resources/TeamMemberResource.php` — add SEO section
- `src/Filament/Resources/NewsResource.php` — add keywords fields
- `src/Filament/Resources/PageResource.php` — add keywords fields
- `src/Filament/Resources/ServiceResource.php` — add keywords fields
- `src/Filament/Pages/SeoSettingsPage.php` — NEW: global SEO defaults

### Views (Frontend)
- `resources/views/layouts/public.blade.php` — add hreflang, keywords meta, og:locale
- `resources/views/public/services/show.blade.php` — use SEOData instead of name/description
- `resources/views/public/pages/show.blade.php` — (already uses SEO from model)
- `resources/views/public/news/show.blade.php` — (already uses SEOData)

### Config / Routes
- `composer.json` — add `spatie/laravel-sitemap`
- `routes/web.php` — maybe sitemap route
- `config/sitemap.php` — new config file

### Translations
- `resources/lang/{ca,es,en}/messages.php` — new translation keys for SEO if needed

---

## 400-Line Budget Forecast

**Risk: MEDIUM-HIGH**

Estimated additions: **500–700 lines** spread across 15+ files.

The work breaks naturally into chained PRs:

| Slice | Scope | Est. Lines | Target |
|---|---|---|---|
| PR1 | Migrations + Eloquent models + Repositories (infrastructure) | ~200 | develop |
| PR2 | Domain entities + Filament resources (admin) | ~250 | develop |
| PR3 | Views + Layout + Sitemap + Config (frontend) | ~200 | develop |

Recommend **auto-chain** strategy — the orchestrator should plan 2-3 chained PRs.

---

## Ready for Proposal
**Yes** — all gaps are well-understood, scope is clear, implementation slices are natural.

Next phase: `sdd-propose` to define the full scope, approach, and rollback plan.
