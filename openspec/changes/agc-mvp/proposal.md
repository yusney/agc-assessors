# Proposal: AGC Assessors MVP — Laravel 13 + Filament 5

**Change:** `agc-mvp`
**Date:** 2026-05-29
**PRD:** `PRD-AGC-Clean-Architecture.md` v2.1
**Status:** Approved — ready for spec phase

---

## Intent

Migrate the AGC Assessors corporate website from a static/headless CMS (Strapi + Astro) to a
**Laravel 13 + Filament 5** stack applying **Strict Clean Architecture**. The goal is a fully
multilingual (ca/es/en) public website with a robust admin panel, dynamic homepage zones, SEO
infrastructure, and contact/lead forms — all maintainable by non-technical staff without code
changes.

---

## Scope

### In Scope
- Full Clean Architecture scaffold: `Domain/` / `Infrastructure/` / `Http/` / `Filament/`
- Multilocale routing (`/ca/`, `/es/`, `/en/`) with session persistence and locale fallback (es → ca)
- Domain entities: Post, Category, Office, Service, Page, ContentSection
- Repository contracts (Domain) + Eloquent implementations (Infrastructure)
- Action pattern for all write operations (CQRS-lite)
- Filament 5 CRUD resources for all entities + homepage zone editor
- Dynamic homepage via `content_sections` relational table (polymorphic, ordered, per-page)
- SEO: `SEOData` DTO, JSON-LD, Open Graph, hreflang, sitemap XML
- Media: Spatie Media Library, WebP transforms, responsive srcset
- Translatable fields via `spatie/laravel-translatable` (JSON in DB)
- Forms: contact, subscription, lead capture, per-department service form
- Accessible navigation: keyboard dropdown, mobile 375px, WCAG AA
- Docker Compose dev environment (PHP 8.4, MariaDB 11.4, Node 24, Redis, Mailpit)
- PHPStan level 8, Pest tests ≥80% coverage on Domain layer

### Out of Scope
- Data migration from current CMS (fresh seed or manual import)
- Biloop API integration (links only)
- Redis distributed cache (file cache for MVP)
- Playwright E2E tests (scaffolded, not implemented)
- Internal search engine
- Any non-MVP Filament workflow (roles/permissions, audit log)

---

## Decisions Locked

| # | Decision | Rule |
|---|----------|------|
| D-1 | **Locale fallback order** | `es` → `ca`. If a translation is missing for the active locale, fall back to Spanish; if also missing in Spanish, fall back to Catalan. |
| D-2 | **Homepage dynamic zones storage** | Separate `content_sections` table with columns `page_id`, `type` (enum), `data` (JSON), `order`. NOT a single JSON blob on the page row. |
| D-3 | **Services/Offices count** | Soft constraint — seeders create exactly 6 of each. Admins CAN add more via Filament. No hard DB/app validation enforcing the number 6. |
| D-4 | **Architecture strictness** | Strict Clean Architecture. `Domain/` is 100% framework-agnostic. All Eloquent, Filament, and Spatie concerns stay in `Infrastructure/`, `Http/`, `Filament/`. No `spatie/laravel-translatable` helpers inside Domain. |
| D-5 | **SEOData location** | `SEOData` is a Domain Value Object. Static factory methods (`fromPost`, `fromPage`, etc.) receive Domain models only — not Eloquent models. Mappers in Infrastructure bridge Eloquent → Domain before SEOData is built. |
| D-6 | **Frontend JS** | Alpine.js for interactive UI (mobile menu, dropdown, reading progress). No Vue/React in MVP. |
| D-7 | **CSS separation** | `tailwind.config.js` at root targets public frontend only. Filament compiles its own Tailwind internally; no shared config. Public CSS target: < 20KB minified + gzip. |

---

## Capabilities

### New Capabilities
- `multilingual-routing`: Locale middleware, URL prefixes (`/ca/`, `/es/`, `/en/`), session persistence, fallback chain
- `posts-management`: Post CRUD with categories, author, SEO fields, translatable slugs, pagination, detail view
- `categories-management`: Category CRUD, translatable slugs, post association
- `offices-management`: Office CRUD, translatable fields, media, map links, seeded data (6 initial)
- `services-management`: Service CRUD, translatable specializations, per-department forms, seeded data (6 initial)
- `dynamic-homepage`: ContentSection table, 6 zone types (Hero, Carousel, Features Grid, Stats Bar, Testimonial, CTA), Filament zone editor
- `static-pages`: Page CRUD (About, legal pages: privacy, cookies, legal, usage), anchored sections
- `seo-infrastructure`: `SEOData` DTO, JSON-LD schemas, Open Graph, hreflang alternates, sitemap XML, canonical URLs
- `contact-forms`: General contact, subscription, lead capture, per-department service forms, FormRequest validation, email notifications
- `media-library`: Spatie Media Library integration, WebP transforms, responsive srcset, centralized media management
- `admin-panel`: Filament 5 resources for all entities, translatable form component, SEO fields per resource, homepage zone editor
- `navigation`: Accessible global nav, keyboard dropdown (Enter/Space/Escape/arrows), language selector, mobile hamburger

### Modified Capabilities
- None (greenfield project — no existing specs to delta)

---

## Approach

**Strict Clean Architecture with thin adapters.**

```
User Request
    │
    ▼
Http/Middleware/SetLocale  (reads URL segment, sets app locale, persists to session)
    │
    ▼
Http/Web/Controllers       (validate input via FormRequest, call Domain Actions)
    │
    ▼
Domain/*/Actions           (pure business logic, depend on Repository interfaces)
    │
    ▼
Domain/*/Repositories (interface)
    │
    ▼
Infrastructure/Persistence/Eloquent/Repositories  (implements interface, uses Mappers)
    │
    ▼
Infrastructure/Persistence/Eloquent/Models        (spatie/translatable, spatie/medialibrary)
    │
    ▼
MariaDB 11.4
```

**Filament** reads/writes through the same Domain Actions and the same Repository interfaces.
No direct Eloquent in Domain. Mappers convert Eloquent → Domain and back.

**Implementation phases** (per PRD §9):
1. Bootstrap + Domain scaffold (entities, VOs, contracts)
2. Infrastructure + migrations + seeders
3. Filament resources + homepage zone editor
4. Public frontend (routes, controllers, views, SEO, forms)
5. Forms + notifications
6. QA, accessibility, PHPStan, coverage

---

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `app/Domain/` | New | All domain entities, actions, VOs, repository contracts |
| `app/Infrastructure/` | New | Eloquent models + mappers + repo implementations |
| `app/Http/` | New | Controllers, requests, view models, locale middleware |
| `app/Filament/` | New | Admin resources, homepage zone editor |
| `app/Providers/AppServiceProvider.php` | New | Interface bindings |
| `database/migrations/` | New | All table migrations incl. `content_sections` |
| `database/seeders/` | New | 6 offices, 6 services, about page, legal pages, admin user |
| `resources/views/` | New | Blade layouts, components, public pages |
| `resources/css/app.css` | New | Tailwind 4 public CSS (target < 20KB) |
| `resources/js/app.js` | New | Alpine.js interactions |
| `routes/web.php` | New | Localized routes with prefix groups |
| `docker/` | New | Docker Compose, Dockerfile, Makefile, setup.sh |
| `openspec/config.yaml` | Update | Lock decisions D-1…D-7 into project config |

---

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Spatie translatable helpers leak into Domain | Medium | PHPStan architecture rules; code review gate before each phase |
| `SEOData::fromPost()` using Eloquent accessors | Medium | Mappers convert Eloquent → Domain model first; static factories receive Domain types only |
| N+1 queries in Filament list views | Medium | Eager load in all repository `getList` methods; Debugbar in dev, checked before QA sign-off |
| Homepage zone editor complexity in Filament | Medium | ContentSection repeater with type-specific sub-forms; isolate in dedicated Filament Page class |
| CSS exceeding 20KB target | Low | Tailwind 4 purge covers only `resources/views/**`; audit during Phase 6 |
| Locale fallback inconsistency in translatable JSON | Low | Implement `LocaleHelper::fallback(string $locale): array` shared across all translation reads |
| MariaDB JSON column performance under load | Low | Index `content_sections.page_id` + `order`; JSON only for zone-specific data |

---

## Rollback Plan

This is a greenfield project with no existing production database. Rollback means:
1. Stop Docker environment (`make down`).
2. Drop the database or delete the Docker volume.
3. Restore previous deployment (static CMS at agcassessors.com) — no data migration was done from it, so no data is lost.

For partial rollbacks during development: each phase is self-contained in migrations. Run `php artisan migrate:rollback --step=N` to undo a specific phase's migrations.

---

## Dependencies

- Docker + Docker Compose installed on dev machine
- MariaDB 11.4 (provided by Docker service)
- PHP 8.4 with all extensions listed in PRD §4.1.2
- `composer install` (Laravel 13, Filament 5, spatie/laravel-translatable, spatie/laravel-medialibrary, laravel-localization, PHPStan, Pest)
- `npm install` (Tailwind 4, Vite 6, Alpine.js)
- SMTP credentials for email notifications (Mailpit in dev)

---

## Success Criteria

- [ ] All public routes respond with correct locale prefix (`/ca/`, `/es/`, `/en/`)
- [ ] Locale fallback chain (es → ca) works for all translatable fields
- [ ] Filament panel allows CRUD for all entities without Eloquent in Domain
- [ ] Homepage zones editable from Filament; frontend renders all 6 zone types
- [ ] SEO: meta tags, Open Graph, JSON-LD, hreflang, sitemap correct on every page
- [ ] No N+1 queries in any public list view (verified with Debugbar)
- [ ] Public CSS < 20KB minified + gzip
- [ ] Images served as WebP with responsive srcset
- [ ] PHPStan level 8 passes with zero errors
- [ ] Domain layer (Actions + Repositories) coverage ≥ 80%
- [ ] Keyboard navigation works on service dropdown (Enter, Space, Escape, arrows)
- [ ] Mobile layout readable at 375px without overflow
- [ ] All forms submit successfully and send email notifications
- [ ] Sitemap XML lists all pages, posts, services, offices
