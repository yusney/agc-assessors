## Exploration: Offices Feature

### Current State
The codebase already follows a Clean Architecture split: domain entities use pure PHP value objects (`TranslatableString`, `Slug`, `SEOData`), infrastructure Eloquent models handle JSON translatables via `spatie/laravel-translatable`, and public pages are rendered through dedicated controllers + Blade views.

Home sections are dispatched by `type` in `resources/views/public/pages/home.blade.php` using `@includeIf('public.home-sections.' . $section->type)`. The navbar reads `menu_items` directly and localizes URLs with `LaravelLocalization::getLocalizedURL(...)`.

### Affected Areas
- `src/Domain/*/Entities/*` — new `Office` entity should mirror existing immutable domain style.
- `src/Domain/*/Repositories/*` — repository interface needed for offices, like News/Service.
- `src/Infrastructure/Persistence/Eloquent/Models/*` — Eloquent office model with translatable JSON fields and casts.
- `src/Infrastructure/Persistence/Eloquent/Repositories/*` — mapper between Eloquent rows and domain entity.
- `src/Filament/Resources/*` — new OfficeResource for CRUD.
- `database/migrations/*` — new `offices` table and possibly home section/menu seed/data updates.
- `app/Http/Controllers/Public/*` — public `/offices` controller.
- `resources/views/public/*` — index page + home section partial for map.
- `resources/lang/*/messages.php` — nav label and page copy.
- `routes/web.php` — localized office routes.
- `resources/views/public/components/navbar.blade.php` — add menu item support via DB or route.

### Approaches
1. **Domain + repository + dedicated public page** — create `Office` entity, repository, Eloquent model, Filament CRUD, and a dedicated `/offices` controller/view; home section reuses the same data.
   - Pros: matches existing architecture; testable; reusable by home section and public page.
   - Cons: more files upfront.
   - Effort: Medium

2. **Admin + page only, no domain abstraction** — keep offices mostly in Eloquent/Filament and render directly in views.
   - Pros: faster initial delivery.
   - Cons: breaks current architecture; duplicates patterns; harder to extend.
   - Effort: Low

### Recommendation
Use the full domain/repository approach. It is consistent with News/Service, keeps translatable fields predictable, and lets the home map and public page share a single source of truth.

### Risks
- Google Maps JS API introduces frontend key/config handling and marker rendering complexity.
- The current navbar is DB-driven; adding a stable “Offices” item may require a menu record/seed rather than a hardcoded link.
- Translatable coordinate/address data must stay JSON-shaped and locale-safe.

### Ready for Proposal
Yes — the next step is to define the Offices delta spec with routes, entity fields, and home-section behavior.
